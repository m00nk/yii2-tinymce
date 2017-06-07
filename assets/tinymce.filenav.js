/**
 * Copyright (C) FIT-Media.com, 2009-2017 {@link http://fit-media.com}
 * Date: 08.05.17, Time: 01:21
 *
 * @author Dmitrij "m00nk" Sheremetjev <admin@fit-media.com>
 */

/**
 * из документации
 * file_browser_callback: function(field_name, url, type, win) { win.document.getElementById(field_name).value = 'my browser value'; }
 */

function filenav(field_name, url, type, win){
	var fmOpts = tinymce.activeEditor.settings.fileManager;
	if(fmOpts)
	{
		var x = tinyMCE.activeEditor.windowManager.open(
			{
				title: fmOpts.medias[type].title,
				width: 960,
				height: 450,
				resizable: false,
				buttons: []
			},
			{
				setUrl: function(url){
					win.document.getElementById(field_name).value = url;
				}
			}
		);
		
		var dlg = x.$el.context;
		var container = $(dlg).find('.mce-window-body');
		var fmContainerId = 'fm-container-7567657-' + fmOpts.id;
		container.html('<div id="' + fmContainerId + '"/>');
		
		fileman.init({
			containerId: fmContainerId,
			url: fmOpts.url,
			csrf: fmOpts.csrf,
			hash: fmOpts.medias[type].hash,
			onSelect: function(url){
				// pass selected file path to TinyMCE
				parent.tinyMCE.activeEditor.windowManager.getParams().setUrl(url);
				
				// force the TinyMCE dialog to refresh and fill in the image dimensions
				var t = parent.tinymce.activeEditor.windowManager.windows[0];
				t.find('#src').fire('change');
				
				// close popup window
				parent.tinyMCE.activeEditor.windowManager.close();
			},
			
			filetypes: fmOpts.medias[type].filetypes,
			maxFileSize: fmOpts.medias[type].maxFileSize
		});
	}
	
	return false;
}



