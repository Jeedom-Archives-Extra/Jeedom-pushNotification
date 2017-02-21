function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
        var _cmd = {};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
	var tr =$('<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">');
	tr.append($('<td>')
		.append($('<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">'))
		.append($('<input class="cmdAttr form-control input-sm" data-l1key="name" value="' + init(_cmd.name) + '" placeholder="{{Name}}" title="Name">')));
	tr.append($('<td>')
			.append($('<input type="hidden" class="cmdAttr" data-l1key="type" value="action" />'))
			.append($('<input type="hidden" class="cmdAttr" data-l1key="subType" value="message" />'))
			.append($('<span>')
				.append($('<input type="checkbox" class="cmdAttr bootstrapSwitch" data-size="mini" data-label-text="{{Afficher}}" data-l1key="isVisible" checked/>'))));
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	}