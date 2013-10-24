(function() {
	tinymce.create('tinymce.plugins.Zedity', {
		init: function(ed,url){
			var t = this;
			t.url = url;
			t.ed = ed;

			ed.addButton('zedity', {
				title: 'Zedity Editor',
				cmd: 'startZedity',
				image: url + '/zedity-logo.png'
			});
			ed.addCommand('startZedity', function(){
				if (t._zedityContent) {
					ed.selection.select(t._zedityContent);
				}
				t._openZedity();
			});

			//manage overlay
			ed.onInit.add(function(ed){
				//show on click over zedity content
				ed.dom.events.add(ed.getBody(), 'mousedown', function(e){
					var parent = ed.dom.getParent(e.target,'div.zedity-editor');
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
				//show on caret inside zedity content
				ed.onNodeChange.add(function(ed,cm,n){
					var parent = ed.dom.getParent(n,'div.zedity-editor');
					if (parent) {
						t._showOverlay(ed,parent);
					} else {
						t._hideOverlay();
					}
				});

				//hide overlay when editor loses focus
				tinyMCE.dom.Event.add(ed.getWin(), 'blur', function(){
					t._hideOverlay();
				});

				//reposition/resize on iframe scroll
				tinyMCE.dom.Event.add(ed.getWin(), 'scroll', function(){
					t._showOverlay(ed,t._zedityContent);
				});
				//reposition/resize on iframe resize
				tinyMCE.dom.Event.add(ed.getWin(), 'resize', function(){
					t._showOverlay(ed,t._zedityContent);
				});

				//reposition/resize on window scroll
				tinyMCE.dom.Event.add(ed.getWin().parent, 'scroll', function(){
					t._showOverlay(ed,t._zedityContent);
				});
				//reposition/resize on window resize
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
			tb_show('Zedity Editor', 'index.php?page=zedity_editor&TB_iframe=true');
			this.open = true;
		},

		_closeZedity: function(){
			var t = this;
			t.open = false;
			setTimeout(function(){
				try {
					t.ed.execCommand('mceFocus',false);
				} catch (e) {}
				t._showOverlay(t.ed,t._zedityContent);
			},100);
		},

		//-------------------------------------------------------------------------------
		//Manage overlay

		_showOverlay: function(ed,n){
			if (!n) return;
			if (this.open) return;

			//disable editing
			ed.getBody().setAttribute('contenteditable', 'false');
			ed.dom.setAttrib(ed.dom.select('a'), 'onclick', 'return false;');

			//exit if not main content editor
			if (ed.id != 'content') return;

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
			var xo = Math.min(X - w.parent.pageXOffset, 0)
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
				this.ed.getBody().setAttribute('contenteditable', 'true');
				this.ed.dom.setAttrib(this.ed.dom.select('a'), 'onclick', null);
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
				src: t.url+'/zedity-logo.png',
				id: 'zedity_button_edit',
				width: '24',
				height: '24',
				title: 'Edit Zedity content'
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
				title: 'Delete Zedity content'
			});

			tinymce.dom.Event.add(zDelButton, 'mousedown', function(e){
				ed.selection.select(t._zedityContent);
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
				author: 'Zedity',
				authorurl: 'http://zedity.com',
				infourl: 'http://zedity.com',
				version: '1.1'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('zedity', tinymce.plugins.Zedity);
})();
