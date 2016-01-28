/**
 * op-core/Template/js/AdminNotice.js
 * 
 * @creation  2015
 * @version   1.0
 * @package   op-core
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
(function(){
//	alert('AdminNotice');

	//	container.
	$container = $('<div/>');
	$container.attr('id','admin-notice');
	$container.text('');
	$('body').append($container);

	//	Each errors.
	jQuery.each(__notice__.errors, function(i, error){
		_add_each_error(i+1, error);
	});

	//	Why two times call???
	__notice__.errors = [];

	//	Event
	$('span.array, span.object').click( _click_object );

	function _click_object(){
		$(this).parent().parent().parent().next().toggle();
	}

	function _add_each_error(no, error){
		_add_headline(no, error);

		//	backtrace
		$table = $('<table/>');
		$table.addClass('backtrace');
		$container.append($table);
		jQuery.each(error.backtrace.reverse(), function(i, backtrace){
			_add_each_backtrace(i+1, backtrace);
		});
	}
	
	function _add_headline(no, error){
		$headline = $('<div/>');
		$headline.addClass('headline');
		$headline.text('#' + no + ' ' + error.message);
		$container.append($headline);
	}

	function _add_each_backtrace(no, backtrace){
		_add_backtrace(no, backtrace);
	}

	function _add_backtrace(no, backtrace){
		var $tr = $('<tr/>');
		$table.append($tr);

		var file = backtrace.file;
		var line = backtrace.line;
		var func = backtrace.function;
		var clas = backtrace.class ? backtrace.class: '';
		var type = backtrace.type  ? backtrace.type:  '';
		var args = _get_args(backtrace);

		var $td = $('<td/>');
		$td.text(no);
		$td.addClass('no');
		$td.addClass('backtrace');
		$tr.append($td);

		var $td = $('<td/>');
		$td.text(file);
		$td.addClass('file');
		$td.addClass('backtrace');
		$tr.append($td);

		var $td = $('<td/>');
		$td.text(line);
		$td.addClass('line');
		$td.addClass('backtrace');
		$tr.append($td);

		var $td = $('<td/>');
		$td.html(clas+type+func+args.html());
		$td.addClass('function');
		$td.addClass('backtrace');
		$tr.append($td);

		if( func === 'AdminNotice' ){
			$tr.addClass('notice');
		}
	}

	function _get_args(backtrace){
		var $args = $('<span/>');
		var html  = '';

		jQuery.each(backtrace.args, function(i, v){
			html += ' ' + _get_value_html(v) + ',';
		});

		html = html.replace(/^ /,'');
		html = html.replace(/,$/,'');
		html = '<span class="args">('+html+')</span>';
		$args.html(html);

		return $args;
	}

	function _add_object(object){
		var $tr = $("<tr/>");
		var $td = $("<td/>");

		//	TR
		$tr.addClass('object');

		//	TD
		$td.attr('colspan',4);
		$td.addClass('object');

		//	append
		$tr.append($td);
		$table.append($tr);
		$td.append( _get_args_table(object) );
	}

	function _get_args_table(args){
		var $table = $('<table/>');
		$table.addClass('object');

		jQuery.each(args, function(i, v){
			var $tr = $('<tr/>');
			var $th = $('<th/>');
			var $td = $('<td/>');

			$table.append($tr);
			$tr.append($th);
			$tr.append($td);

			$th.addClass('args');
			$td.addClass('args');

			$th.html(i);
			$td.html( v );

			$td.html( _get_value_html(v) );
		});

		return $table;
	}

	function _get_value_html(v){
		var type = typeof v;

		if( v === null ){
			type = 'null';
		}

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
				v = '<span class="string">'+v+'</span>';
				break;
			case 'object':
				_add_object(v);
				if( v.hasOwnProperty(0) ){
					v = '<span class="array">array...</span>';
				}else{
					v = '<span class="object">object...</span>';
				}
				break;
			defualt:
				alert(type);
		}
		return v;
	}
})();
