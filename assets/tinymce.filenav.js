/**
 * Copyright (C) FIT-Media.com, 2009-2015 {@link http://fit-media.com}
 * Date: 05.04.15, Time: 16:04
 *
 * @author Dmitrij "m00nk" Sheremetjev <admin@fit-media.com>
 */

/**
 * из документации
 * file_browser_callback: function(field_name, url, type, win) { win.document.getElementById(field_name).value = 'my browser value'; }
 */
function tinymce_filenav(field_name, url, type, win)
{
	console.log("T", type);
	console.log(tinyMCE.settings.fileManager[type]);
	
	// tinyMCE.activeEditor.windowManager.open(
	// 	{
	// 		file: tinyMCE.settings.fileManagerPath + '&filter=' + type, // use an absolute path!
	// 		title: 'elFinder',
	// 		width: 960,
	// 		height: 450,
	// 		resizable: 'no'
	// 	},
	// 	{
	// 		setUrl: function(url)
	// 		{
	// 			win.document.getElementById(field_name).value = url;
	// 		}
	// 	}
	// );
	return false;
}

function tinymce_filenav_add_file(file)
{
	// pass selected file path to TinyMCE
	parent.tinyMCE.activeEditor.windowManager.getParams().setUrl(file.url);

	// force the TinyMCE dialog to refresh and fill in the image dimensions
	var t = parent.tinymce.activeEditor.windowManager.windows[0];
	t.find('#src').fire('change');

	// close popup window
	parent.tinyMCE.activeEditor.windowManager.close();
}

