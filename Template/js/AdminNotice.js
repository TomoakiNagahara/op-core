
(function(){
	alert('AdminNotice');

	//	container.
	$container = $('<div/>');
	$container.attr('id','admin-notice');
	$container.text('');
	$('body').append($container);

	//	Each errors.
	jQuery.each(__notice__.errors, function(i, error){
		_generate_each_error(i+1, error);
	});

	function _generate_each_error(no, error){
		_generate_headline(no, error);

		//	backtrace
		$table = $('<table/>');
		$table.addClass('backtrace');
		$container.append($table);
		jQuery.each(error.backtrace, function(i, backtrace){
			_generate_each_backtrace(i+1, backtrace);
		});
	}
	
	function _generate_headline(no, error){
		$headline = $('<div/>');
		$headline.addClass('headline');
		$headline.text('#' + no + ' ' + error.message);
		$container.append($headline);
	}

	function _generate_each_backtrace(no, backtrace){
		_generate_backtrace(no, backtrace);
	}

	function _generate_backtrace(no, backtrace){
		console.dir(backtrace);
		var file = backtrace.file;
		var line = backtrace.line;
		var func = backtrace.function;
		var clas = backtrace.class ? backtrace.class: '';
		var type = backtrace.type  ? backtrace.type:  '';
		var args = _generate_args(backtrace);

		var $tr = $('<tr/>');
		$table.append($tr);

		var $td = $('<td/>');
		$td.text(no);
		$td.addClass('no');
		$tr.append($td);

		var $td = $('<td/>');
		$td.text(file);
		$td.addClass('file');
		$tr.append($td);

		var $td = $('<td/>');
		$td.text(line);
		$td.addClass('line');
		$tr.append($td);

		var $td = $('<td/>');
		$td.html(clas+type+func+args);
		$td.addClass('function');
		$tr.append($td);
	}

	function _generate_args(backtrace){
		var args = '';
		jQuery.each(backtrace.args, function(i, v){
			var type = typeof v;
			switch( type ){
				case 'null':
					v = '<span class="null">null</span>';
					break;
				case 'boolean':
					if( v ){
						v = '<span class="true">true</span>';
					}else{
						v = '<span class="false">false</span>';
					}
					break;
				case 'number':
					v = '<span class="number">'+v+'</span>';
					break;
				case 'string':
					v = '<span class="string">\''+v+'\'</span>';
					break;
				case 'object':
					v = '<span class="object">object</span>';
					break;
				defualt:
					alert(type);
			}
			args += ' '+v+',';
		});
		args = args.replace(/^ /,'');
		args = args.replace(/,$/,'');
		return '('+args+')';
	}
})();
