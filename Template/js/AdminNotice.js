
(function(){
	alert('AdminNotice');
	
	//	container.
	$container = $('<div id="__notice__"> container </div>');
	$('body').append($container);
	
	//	Each errors.
	jQuery.each(__notice__.errors, function(i, error){
		var no = i + 1;
		var date = error.timestamp;
		$h2 = $('<h2/>');
		$h2.html('#' + no + ' ' + error.message);
		$container.append($h2);

		//	backtrace
		jQuery.each(error.backtrace, function(i, backtrace){
			var $div  = $('<div/>');

			//	div.backtrace
			$div.addClass('backtrace');

			//	head
			$head = _generate_head(i+1, backtrace);

			//	append
			$container.append($div)
			$div.append($head);
		});
	});

	function _generate_head(i, backtrace){
		console.dir(backtrace);

		var $head = $('<p/>');
		var file = backtrace.file;
		var line = backtrace.line;
		var func = backtrace.function;
		var clas = backtrace.class;
		var type = backtrace.type;
		var args = backtrace.args;

		//	file and line
		$head.text(i+' '+file+' '+line+' '+clas+type+func+'()' );

		return $head;
	}
})();

