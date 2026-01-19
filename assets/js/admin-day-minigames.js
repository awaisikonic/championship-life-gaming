(function($){
  function renumber($tbody){
    $tbody.find('tr').each(function(i){
      $(this).find('.clg-sort').text(i+1);
      $(this).find('input.clg-sort-order').val(i+1);
    });
  }

  $(function(){
    var $table = $('#clg-day-minigames-table');
    if(!$table.length) return;

    var $tbody = $table.find('tbody');

    $tbody.sortable({
      items: 'tr',
      handle: '.clg-handle',
      axis: 'y',
      update: function(){ renumber($tbody); }
    });

    $('#clg-add-minigame').on('click', function(e){
      e.preventDefault();
      var id = parseInt($('#clg-minigame-select').val() || '0', 10);
      var title = $('#clg-minigame-select option:selected').text();
      if(!id) return;

      // prevent duplicates
      if($tbody.find('input.clg-minigame-id[value="'+id+'"]').length){
        alert('This mini-game is already added.');
        return;
      }

      var idx = $tbody.find('tr').length + 1;
      var row = ''+
        '<tr>'+
          '<td class="clg-handle" style="cursor:move">☰</td>'+
          '<td><span class="clg-sort">'+idx+'</span><input type="hidden" class="clg-sort-order" name="clg_day_minigames[sort_order][]" value="'+idx+'"></td>'+
          '<td>'+ $('<div>').text(title).html() +'<input type="hidden" class="clg-minigame-id" name="clg_day_minigames[minigame_id][]" value="'+id+'"></td>'+
          '<td><button type="button" class="button clg-remove">Remove</button></td>'+
        '</tr>';
      $tbody.append(row);
      renumber($tbody);
    });

    $tbody.on('click', '.clg-remove', function(){
      $(this).closest('tr').remove();
      renumber($tbody);
    });
  });
})(jQuery);
