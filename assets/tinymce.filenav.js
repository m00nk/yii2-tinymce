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
	tinyMCE.activeEditor.windowManager.open(
		{
			file: tinyMCE.settings.fileManagerPath+'&filter=' + type, // use an absolute path!
			title: 'elFinder 2.0',
			width: 960,
			height: 450,
			resizable: 'no'
		},
		{
			setUrl: function (url)
			{
				win.document.getElementById(field_name).value = url;
			}
		}
	);
	return false;
}

function elFinderTest(file)
{
	alert('OK');
}

