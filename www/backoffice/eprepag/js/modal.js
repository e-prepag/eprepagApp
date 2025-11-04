$(function(){
   $('.btn-question').click(function(e){
       e.preventDefault();
       
       var msg = $(this).attr('data-msg');
       
       showMessage(msg);
   }); 
});

function showMessage(msg){
    $('<div class="msgbox" style="height: auto !important;">'+msg+'</div>').fancybox().trigger('click');
}