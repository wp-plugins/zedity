(function() {
    tinymce.create('tinymce.plugins.Zedity', {
        init: function(ed,url){
			ed.addButton('zedity', {
                title : 'Zedity Editor',
                cmd : 'startZedity',
                image : url + '/zedity-logo.png'
            });
			ed.addCommand('startZedity', function() {
				tb_show('Zedity Editor', 'index.php?page=zedity_editor&TB_iframe=true');
            });
        },
        createControl: function(n,cm){
            return null;
        },
        getInfo: function(){
            return {
                longname : 'Zedity Editor',
                author : 'Zedity',
                authorurl : 'http://zedity.com',
                infourl : 'http://zedity.com',
                version : '1.0'
            };
        }
    });
 
    // Register plugin
    tinymce.PluginManager.add('zedity', tinymce.plugins.Zedity);
})();