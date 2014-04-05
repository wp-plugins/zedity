(function() {
	tinymce.create('tinymce.plugins.Zedity', {
		init: function(ed,url){
			var t = this;
			t.url = url;
			t.ed = ed;

			ed.addButton('zedity', {
				title: 'Zedity Editor',
				cmd: 'startZedity',
				// NOTE: at the moment, the deploy scripts renames zedity-logo-premium.png into zedity-logo.png
				image: url + '/zedity-logo.png?' + this.getInfo().version
			});
			ed.addCommand('startZedity', function(){
				if (t._zedityContent) {
					ed.selection.select(t._zedityContent);
				}
				t._openZedity();
			});

			ed.onPreInit.add(function(){
				//be sure iframes are allowed
				ed.schema.addValidElements('iframe[*]');
				//set zedity wrapper size when in visual editor (to show full overlay)
				ed.parser.addNodeFilter('div', function(nodes){
					for (var i=nodes.length-1; i>=0; --i) {
						if (!nodes[i].attributes.map.class || nodes[i].attributes.map.class.indexOf('zedity-iframe-wrapper')==-1) continue;
						if (!nodes[i].attributes.map['data-origw'] || !nodes[i].attributes.map['data-origh']) continue;
						nodes[i].attr({
							style: 'width:'+nodes[i].attributes.map['data-origw']+'px;height:'+nodes[i].attributes.map['data-origh']+'px'
						});
					}
				});
				//set zedity wrapper real css on save
				ed.serializer.addNodeFilter('div', function(nodes,name,args){
					for (var i=nodes.length-1; i>=0; --i) {
						if (!nodes[i].attributes.map.class || nodes[i].attributes.map.class.indexOf('zedity-iframe-wrapper')==-1) continue;
						if (!nodes[i].attributes.map['data-origw'] || !nodes[i].attributes.map['data-origh']) continue;
						nodes[i].attr({
							'data-mce-style': 'max-width:'+nodes[i].attributes.map['data-origw']+'px;max-height:'+nodes[i].attributes.map['data-origh']+'px'
						});
					}
				});
				//temporarily disabled iframe management
				/*
				//set iframe wrapper size when in visual editor (to show full overlay)
				ed.parser.addNodeFilter('iframe', function(nodes){
					for (var i=nodes.length-1; i>=0; --i) {
						if (!nodes[i].attributes.map.class || nodes[i].attributes.map.class.indexOf('zedity-iframe')==-1) continue;
						if (!nodes[i].parent) {
							alert('Warning! Something is not right. A plugin may be interfering with the Zedity iframe.\n\nIn case of errors please provide the list of all plugins you have active at this time or try disabling the other plugins.');
							break;
						} else {
							nodes[i].parent.attr({
								style: 'width:'+nodes[i].attributes.map.width+'px;height:'+nodes[i].attributes.map.height+'px'
							});
						}
					}
				});
				//set iframe wrapper real css on save
				ed.serializer.addNodeFilter('iframe', function(nodes,name,args){
					for (var i=nodes.length-1; i>=0; --i) {
						if (!nodes[i].attributes.map.class || nodes[i].attributes.map.class.indexOf('zedity-iframe')==-1) continue;						
						if (!nodes[i].parent) {
							alert('Warning! Something is not right. A plugin may be interfering with the Zedity iframe.\n\nIn case of errors please provide the list of all plugins you have active at this time or try disabling the other plugins.');
							break;
						} else {
							nodes[i].parent.attr({
								'data-mce-style': 'max-width:'+nodes[i].attributes.map.width+'px;max-height:'+nodes[i].attributes.map.height+'px'
							});
						}
					}
				});
				*/
			});

			//manage overlay
			ed.onInit.add(function(ed){
				//show overlay on click over zedity content
				ed.dom.events.add(ed.getBody(), 'mousedown', function(e){
					var parent = ed.dom.getParent(e.target,'div.zedity-editor') || ed.dom.getParent(e.target,'div.zedity-iframe-wrapper');
					if (parent) {
						try {
							ed.execCommand('mceFocus',false);
						} catch (e) {}
						t._showOverlay(ed,parent);
						ed.dom.events.prevent(e);
						ed.dom.events.stop(e);
						ed.plugins.wordpress._hideButtons();
					}
				});
				//show overlay on caret inside zedity content
				ed.onNodeChange.add(function(ed,cm,n){
					var parent = ed.dom.getParent(n,'div.zedity-editor') || ed.dom.getParent(n,'div.zedity-iframe-wrapper');
					if (parent) {
						t._showOverlay(ed,parent);
					} else {
						t._hideOverlay();
					}
				});
				//block enter key when overlay is open
				ed.dom.events.add(ed.getBody(), 'keydown', function(e){
					if (e.keyCode==13) {
						var n = tinymce.DOM.get('zedity_content_overlay');
						if (n && n.style.display=='block') {
							ed.dom.events.prevent(e);
							ed.dom.events.stop(e);
						}
					} else if (e.keyCode==46 || e.keyCode==8) {
						if (ed.selection.isCollapsed()) {
							var n = ed.selection.getNode();
							if (n.getAttribute('class') && n.getAttribute('class').indexOf('zedity')>-1) {
								ed.dom.events.prevent(e);
								ed.dom.events.stop(e);
								return;
							}
							var nc = n.textContent;
							var empty = (nc.replace(/^\s+|\s+$/g, '') === '');
							if (empty) {
								tinymce.DOM.remove(n);
								ed.dom.events.prevent(e);
								ed.dom.events.stop(e);
							}
						}
					}
				});

				//hide overlay when editor loses focus
				tinyMCE.dom.Event.add(ed.getWin(), 'blur', function(){
					t._hideOverlay();
				});

				//reposition/resize overlay on WP editor scroll
				tinyMCE.dom.Event.add(ed.getWin(), 'scroll', function(){
					t._showOverlay(ed,t._zedityContent);
				});
				//reposition/resize overlay on WP editor resize
				tinyMCE.dom.Event.add(ed.getWin(), 'resize', function(){
					t._showOverlay(ed,t._zedityContent);
				});

				//reposition/resize overlay on window scroll
				tinyMCE.dom.Event.add(ed.getWin().parent, 'scroll', function(){
					t._showOverlay(ed,t._zedityContent);
				});
				//reposition/resize overlay on window resize
				tinyMCE.dom.Event.add(ed.getWin().parent, 'resize', function(){
					t._showOverlay(ed,t._zedityContent);
				});
			});

			t._createOverlay(ed);
		},
		createControl: function(n,cm){
			return null;
		},

		_openZedity: function(){
			this._hideOverlay(true);
			tb_show('Zedity Editor', 'admin-ajax.php?action=zedity_editor&TB_iframe=true');
			this.open = true;
		},

		_closeZedity: function(){
			var t = this;
			t.open = false;
			setTimeout(function(){
				try {
					t.ed.execCommand('mceFocus',false);
				} catch (e) {}
				if (!t._zedityContent) {
					var n = t.ed.selection.getNode();
					var z = t.ed.dom.select('.zedity-editor',n)[0] || t.ed.dom.select('.zedity-iframe-wrapper',n)[0];
					t._zedityContent = z;
				}
				t._showOverlay(t.ed,t._zedityContent);
			},100);
		},

		//-------------------------------------------------------------------------------
		//Manage overlay

		_showOverlay: function(ed,n){
			if (!n) return;
			if (this.open) return;

			/*
			//disable editing
			ed.getBody().setAttribute('contenteditable', 'false');
			//avoid clicking on anchors
			var t = this;
			parent.jQuery('a',ed.getBody()).off('click.zedity-mce').on('click.zedity-mce',function(){
				t.ed.selection.select(this);
				t.ed.selection.collapse(true);
				t.ed.nodeChanged();
				ed.plugins.wordpress._hideButtons();
				t._hideOverlay();
				return false;
			});
			*/

			//exit if it is the fullscreen editor (@qtranslate)
			if (ed.id == 'wp_mce_fullscreen') return;

			var w = ed.getWin();
			var vp = ed.dom.getViewPort(ed.getWin());
			var p1 = tinymce.DOM.getPos(ed.getContentAreaContainer());
			var p2 = ed.dom.getRect(n);
			var d = {
				w: ed.getDoc().documentElement.scrollWidth,
				h: ed.getDoc().documentElement.scrollHeight
			};
			var barh = (function(){
				var h = 0;
				if (tinymce.DOM.select('#wpadminbar')[0] && (getComputedStyle(tinymce.DOM.select('#wpadminbar')[0]).display != 'none')) {
					h = tinymce.DOM.getRect('wpadminbar').h;
				}
				return h;
			})();

			//calculate position
			var X = Math.max(p2.x - vp.x, 0) + p1.x;
			var Y = Math.max(p2.y - vp.y, 0) + p1.y;
			var xo = Math.min(X - w.parent.pageXOffset, 0);
			var yo = Math.min(Y - w.parent.pageYOffset - barh, 0);
			X = Math.max(X, w.parent.pageXOffset);
			Y = Math.max(Y, w.parent.pageYOffset + barh);

			//calculate size
			if (d.h>vp.h && this.sb) vp.w -= this.sb.w;
			if (d.w>vp.w && this.sb) vp.h -= this.sb.h;
			var w = Math.min(p2.x - vp.x, 0) + p2.w;
			var h = Math.min(p2.y - vp.y, 0) + p2.h;
			w = Math.min(w, vp.w-(p2.x-vp.x), vp.w) + xo;
			h = Math.min(h, vp.h-(p2.y-vp.y), vp.h) + yo;

			//not enough room for buttons, exit
			//this should be the smallest rectangle that can accommodate the buttons (90x50),
			//now Zedity content can be even smaller (MIN_WIDTH and MIN_HEIGHT values in zedity.php), so we let it anyway
			if (w<50 || h<20) {
				this._hideOverlay(true);
				return;
			}

			//show overlay
			tinymce.DOM.setStyles('zedity_content_overlay', {
				left: X+'px',
				top: Y+'px',
				width: w+'px',
				height: h+'px',
				display: 'block'
			});

			//keep overlay while scrolling (avoid onNodeChange)
			ed.selection.select(n.firstChild);
			ed.selection.collapse(false);
			
			this._zedityContent = n;
		},
		_hideOverlay: function(block){
			tinymce.DOM.hide(tinymce.DOM.select('#zedity_content_overlay'));
			//re-enable editing
			if (!block) {
				/*
				this.ed.getBody().setAttribute('contenteditable', 'true');
				//re-enable clicking on anchors
				parent.jQuery('a',this.ed.getBody()).off('click.zedity-mce');
				*/
				this._zedityContent = null;
			}
		},
		_createOverlay: function(ed){
			var t = this;
			if (tinymce.DOM.get('zedity_content_overlay')) return;

			//calculate scrollbars
			this.sb = (function(){
				var inner = document.createElement('p');
				inner.style.width = '100%';
				inner.style.height = '100%';
				var outer = document.createElement('div');
				outer.style.position = 'absolute';
				outer.style.top = '0px';
				outer.style.left = '0px';
				outer.style.visibility = 'hidden';
				outer.style.width = '100px';
				outer.style.height = '100px';
				outer.style.overflow = 'hidden';
				outer.appendChild(inner);
				document.body.appendChild(outer);
				var w1 = inner.offsetWidth;
				var h1 = inner.offsetHeight;
				outer.style.overflow = 'scroll';
				var w2 = inner.offsetWidth;
				var h2 = inner.offsetHeight;
				if (w1 == w2) w2 = outer.clientWidth;
				if (h1 == h2) h2 = outer.clientHeight;
				document.body.removeChild(outer);
				return { w: (w1 - w2), h: (h1 - h2) };
			})();

			tinymce.DOM.add(document.body, 'div', {
				id: 'zedity_content_overlay'
			});

			var zEditButton = tinymce.DOM.add('zedity_content_overlay', 'img', {
				// NOTE: at the moment, the deploy scripts renames zedity-logo-premium.png into zedity-logo.png
				src: t.url+'/zedity-logo.png?' + this.getInfo().version,
				id: 'zedity_button_edit',
				width: '24',
				height: '24',
				title: ed.getLang('zedity.edit_content')
			});

			tinymce.dom.Event.add(zEditButton, 'mousedown', function(e){
				ed.selection.select(t._zedityContent);
				t._openZedity();
			});

			var zDelButton = tinymce.DOM.add('zedity_content_overlay', 'img', {
				src: t.url+'/delete.png',
				id: 'zedity_button_del',
				width: '24',
				height: '24',
				title: ed.getLang('zedity.delete_content')
			});

			tinymce.dom.Event.add(zDelButton, 'mousedown', function(e){
				var n = t._zedityContent;
				//if is the inner content, get the outer wrapper
				if (tinymce.DOM.hasClass(n,'zedity-iframe-wrapper') || tinymce.DOM.hasClass(n,'zedity-editor')) {
					n = ed.dom.getParent(n,'div.zedity-wrapper');
				}
				ed.selection.select(n);
				t._hideOverlay();
				try {
					ed.execCommand('mceStartTyping','');
				} catch (e) {}
				tinymce.DOM.remove(ed.selection.getNode());
				try {
					ed.execCommand('mceEndTyping','');
				} catch (e) {}
			});
		},

		getInfo: function(){
			return {
				longname: 'Zedity Editor',
				author: 'Zuyoy LLC',
				authorurl: 'http://zedity.com',
				infourl: 'http://zedity.com',
				version: '2.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('zedity', tinymce.plugins.Zedity);
})();
