
  <div class="modal fade" id="modalDiv" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         <h4 class="modal-title" id="modalTitle"></h4>
       </div>
       <div id="modalContent">
       </div>
     </div>
   </div>
  </div>

  <div class="modal fade" id="subsDiv" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         <h4 class="modal-title" id="subsTitle"></h4>
       </div>
       <div id="subsContent">
       </div>
     </div>
   </div>
  </div>
  <script type="text/javascript" src="//code.jquery.com/jquery-3.3.1.min.js"></script>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
  <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="//unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <link href="//gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <script src="//gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"></script>
  <script src="/public/admin/bootstrap-select.js"></script>
  <script type="text/javascript" src="/public/datepicker/js/bootstrap-datepicker.min.js"></script>
  <script type="text/javascript" src="/public/datepicker/locales/bootstrap-datepicker.tr.min.js"></script>
  <script src="/public/admin/toastDemo.js"></script>
  <script src="/public/admin/script.js"></script>
  <script src="/public/admin/script-2.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <script src="/public/admin/jquery.tinytoggle.min.js"></script>
  <script type="text/javascript">
      $(document).ready(function(){
        var site_url  = $('head base').attr('href');
        <?php if( route(2) == "new-service" || route(2) == "new-subscription" ): echo '$(document).ready(function(){
          getProviderServices($("#provider").val(),site_url);
        });'; endif; ?>
        <?php if( $error ): ?>
          $.toast({
              heading: 'Failed',
              text: '<?php echo $errorText; ?>',
              icon: 'error',
              loader: true,
              loaderBg: '#9EC600'
          })
        <?php endif; ?>
        <?php if( $success ): ?>
          $.toast({
              heading: 'Başarılı',
              text: '<?php echo $successText; ?>',
              icon: 'success',
              loader: true,
              loaderBg: '#9EC600'
          })
        <?php endif; ?>

            /*Summernote editor*/
            $('#summernoteExample').summernote({
              height: 300,
              tabsize: 2
            });

      $(".service-sortable").sortable({handle: '.handle',
        update: function(event, ui) {
          var array = [];
            $(this).find('tr').each(function(i) {
                $(this).attr('data-line',i+1);
                var params = {};
                params['id']   = $(this).attr('data-id');
                params['line'] = $(this).attr('data-line');
                array.push(params);
            });
            $.post(site_url+'admin/ajax_data',{action:'service-sortable',services:array});
        }
    });

    $(".methods-sortable").sortable({handle: '.handle',
      update: function(event, ui) {
        var array = [];
          $(this).find('tr').each(function(i) {
              $(this).attr('data-line',i+1);
              var params = {};
              params['id']   = $(this).attr('data-id');
              params['line'] = $(this).attr('data-line');
              array.push(params);
          });
          $.post(site_url+'admin/ajax_data',{action:'paymentmethod-sortable',methods:array});
      }
  });

      $(".category-sortable").sortable({handle: '.handle',
          update: function(event, ui) {
            var array = [];
              $(this).find('.categories').each(function(i) {
                  $(this).attr('data-line',i+1);
                  var params = {};
                  params['id']   = $(this).attr('data-id');
                  params['line'] = $(this).attr('data-line');
                  array.push(params);
              });
              $.post(site_url+'admin/ajax_data',{action:'category-sortable',categories:array});
          }
      });

      });
      $(function () {
        $('[data-toggle="tooltip"]').tooltip()
      });
      <?php if( route(2) == "themes" && route(3) ): ?>
      (function () {
          var codeMirroSetting = {},
              codeType = '<?=$codeType;?>';

          switch (codeType){
              case 'twig':
                  codeMirroSetting = {
                      mode : "text/html",
                      lineNumbers : true,
                      profile: 'xhtml',
                      lineWrapping: true,
                      extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
                      foldGutter: true,
                      gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                      onKeyEvent: function(i, e) {
                          // Hook into F11
                          if ((e.keyCode == 122 || e.keyCode == 27) && e.type == 'keydown') {
                              e.stop();
                              return toggleFullscreenEditing();
                          }
                      },
                  };
                  break;
              case 'css':
                  codeMirroSetting = {
                      mode : "text/css",
                      lineNumbers : true,
                      lineWrapping: true,
                      extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
                      foldGutter: true,
                      gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                      onKeyEvent: function(i, e) {
                          // Hook into F11
                          if ((e.keyCode == 122 || e.keyCode == 27) && e.type == 'keydown') {
                              e.stop();
                              return toggleFullscreenEditing();
                          }
                      },
                  };
                  break;
              case 'js':
                  codeMirroSetting = {
                      mode : "text/javascript",
                      lineNumbers : true,
                      lineWrapping: true,
                      extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
                      foldGutter: true,
                      gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                      onKeyEvent: function(i, e) {
                          // Hook into F11
                          if ((e.keyCode == 122 || e.keyCode == 27) && e.type == 'keydown') {
                              e.stop();
                              return toggleFullscreenEditing();
                          }
                      },
                  };
                  break;
              default:
                  codeMirroSetting = {
                      lineNumbers : true,
                      lineWrapping: true,
                      foldGutter: true,
                      gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                      onKeyEvent: function(i, e) {
                          // Hook into F11
                          if ((e.keyCode == 122 || e.keyCode == 27) && e.type == 'keydown') {
                              e.stop();
                              return toggleFullscreenEditing();
                          }
                      },
                  };
                  break;
          }

          CodeMirror.fromTextArea(document.getElementById("code"), codeMirroSetting);

          function toggleFullscreenEditing()
          {
              var editorDiv = $('.CodeMirror-scroll');
              if (!editorDiv.hasClass('fullscreen')) {
                  toggleFullscreenEditing.beforeFullscreen = { height: editorDiv.height(), width: editorDiv.width() }
                  editorDiv.addClass('fullscreen');
                  editorDiv.height('100%');
                  editorDiv.width('100%');
                  editor.refresh();
                  editorDiv.append('<div class="fullscreen-blockFull"><a href="#" class="btn btn-sm btn-default fullScreenButtonOff"><span class="fa fa-compress" style="font-size: 18px; position: absolute; left: 6px; top: 4px;"></span></a> </div>')
              }
              else {
                  editorDiv.removeClass('fullscreen');
                  editorDiv.height(toggleFullscreenEditing.beforeFullscreen.height);
                  editorDiv.width(toggleFullscreenEditing.beforeFullscreen.width);
                  editor.refresh();
                  $('.fullscreen-blockFull').remove();
              }
          }

          $(document).on('click', '.fullScreenButton', function (e) {
              toggleFullscreenEditing();
          });
          $(document).on('click', '.fullScreenButtonOff', function (e) {
              toggleFullscreenEditing();
          });
          $(document).keyup(function(e) {
              if (e.keyCode == 27 && $('.fullscreen').length >= 1) {
                   toggleFullscreenEditing();
              }
          });
      })();
      <?php endif; ?>
  </script>
  <body style="color: rgb(18, 18, 18); background-color: rgb(255, 255, 255);">
  </body>
<h4 style="text-align: center;"><strong>Developed By : <a href="https://themesave.com/" target="_blank">THEMESAVE</a></strong></h4>
</body>
</html>
</html>