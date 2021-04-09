(function() {
	tinymce.create('tinymce.plugins.wppyButtons', {
		init: function(ed, url) {
			ed.addCommand('wppy_code',
			function() {
				var inputText = prompt ('添加注音') || '';
				var value = tinyMCE.activeEditor.selection.getContent();
				if( ! value.length )
					return false;
				if (inputText.length) {
					var replacementText = "<ruby>"+value+"<rp>(</rp><rt>" + inputText + "</rt><rp>)</rp></ruby>";
				}
				tinyMCE.activeEditor.selection.setContent(replacementText);
			});

			ed.addButton('wppy_code', {
				title: '添加注音',
				cmd: 'wppy_code',
				icon: 'wppy-code',
				image : url + '/../images/wppy.svg',
			});
		},
		createControl: function(n, cm) {
			return null;
		},
		getInfo: function() {
			return null;
		}
	});
	tinymce.PluginManager.add('wppy_code_button', tinymce.plugins.wppyButtons);
})();