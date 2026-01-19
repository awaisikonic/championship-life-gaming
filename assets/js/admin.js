(function($){
  function ensureEmptyMessage($list){
    var hasItems = $list.find('li.clg-item').length > 0;
    var $empty = $list.find('li.clg-empty');
    if(hasItems){
      $empty.remove();
    } else {
      if(!$empty.length){
        $list.append('<li class="clg-empty" style="padding:10px;border:1px dashed #c3c4c7;background:#f6f7f7;">No mini-games assigned yet. Use the dropdown above to add.</li>');
      }
    }
  }

  $(function(){
    var $list = $('#clg_minigame_sortable');
    if(!$list.length) return; // Only on Day edit screen.

    $list.sortable({
      items: 'li.clg-item',
      handle: '.dashicons-move',
      axis: 'y'
    });

    $('#clg_add_minigame_btn').on('click', function(e){
      e.preventDefault();

      var $select = $('#clg_add_minigame');
      var id = parseInt($select.val() || '0', 10);
      if(!id) return;

      // prevent duplicates
      if($list.find('input[name="cl_day_minigame_ids[]"][value="'+id+'"]').length){
        alert('This mini-game is already added.');
        return;
      }

      var title = $select.find('option:selected').text();
      var safeTitle = $('<div>').text(title).html();

      var li = ''+
        '<li class="clg-item" data-id="'+id+'" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #dcdcde;background:#fff;margin-bottom:6px;cursor:move;">'+
          '<span class="dashicons dashicons-move" aria-hidden="true"></span>'+
          '<strong style="flex:1;">'+safeTitle+'</strong>'+
          '<code>ID: '+id+'</code>'+
          '<input type="hidden" name="cl_day_minigame_ids[]" value="'+id+'" />'+
          '<button type="button" class="button-link-delete clg-remove" style="margin-left:8px;">Remove</button>'+
        '</li>';

      $list.append(li);
      ensureEmptyMessage($list);
    });

    $list.on('click', '.clg-remove', function(){
      $(this).closest('li').remove();
      ensureEmptyMessage($list);
    });

    ensureEmptyMessage($list);
  });
})(jQuery);
