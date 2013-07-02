/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
        config.toolbar_Full =
[
	{ name: 'document', items : [ 'Source','-','Templates' ] },
	{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','- ',
                'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'  ] },
        	{ name: 'colors', items : [ 'TextColor','BGColor' ] },
	{ name: 'links', items : [ 'Link','Unlink','Anchor' ,'Image','Flash','Table'] },
	'/',
	{ name: 'styles', items : [ 'Format','FontSize' ] },
];
};
