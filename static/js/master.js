(function($){
    
    $(document).ready(
        function() {
            
            $('.control.file-picker').on(
                'click',
                function(event){
                    $('.bundle-picker').click();
                    //alert('Clicked');
                }
            );
            
            $('.bundle-picker').on(
                'change',
                function(event){
                    //alert($(this).val());
                    var files = event.originalEvent.target.files || event.originalEvent.dataTransfer.files;
                    
                    if (window.XMLHttpRequest){
                        var id = $('body').modal_manager('wait', 'Uploading...');
                        var $model = $('#' + id);
                        $model.find('.progress-bar').css('width', '0');
                        
                        var xhr = new XMLHttpRequest();
                        
                        xhr.upload.addEventListener(
                            'progress',
                            function(event){
                                if (event.lengthComputable){
                                    var $progress = $('.progress-bar');
                                    
                                    $progress.css('width', (event.loaded / event.total) * 100 + "%");
                                }
                            }
                        );
                        
                        xhr.onreadystatechange = function(event){
                            if (this.readyState == 4 && this.status == 200){
                                eval("var data = " + this.responseText);
                                //alert('data: ' + data);
                                //console.log(data);
                                //if (typeof data === 'object'){
                                    if (data['upload-bundle'].status == 'error') {
                                        
                                        var options = {
                                            title: 'Error processing bundle',
                                            header: true
                                        };
                                        
                                        var $message = $("<div><p><strong>The following errors occurred while processing the bundle:</strong><ul></ul></div>");
                                        
                                        for(var i = 0; i < data['upload-bundle']['errors'].length; i++){
                                            $message.find('ul').append('<li>' + data['upload-bundle']['errors'][i] + '</li>');
                                        }
                                        
                                        $('#' + id).modal('hide');
                                        
                                        
                                        id = $('body').modal_manager('alert', $message, options);
                                    }else if (data['upload-bundle'].status == 'success') {
                                        //alert('success id:' + id);
                                        $('#' + id).find('.message').empty().append(data['upload-bundle']['display']);
                                        
                                        $.ajax({
                                            url: data['upload-bundle']['next_action'],
                                            success: process_result
                                        });
                                        //$.get(
                                        //    data['upload-bundle']['next_action'],
                                        //    process_result(v)
                                        //);
                                    }
                                //}
                                
                                //if (data && data.filename) {
                                //    $file = view_file_upload_get_item_by_name(data.filename);
                                //    $file.addClass('complete');
                                //    console.log(data);
                                //    var callback = $('.view.file_upload input[name="callback_function"]').val();
                                //    if (callback != ''){
                                //        var code = callback + "(" + data.file_id + ");";
                                //        eval(code);
                                //    }
                                //}
                                //alert(this.responseText);
                            }
                        }
                        
                        xhr.open('post', "/bundle-details");
                        
                        if (window.FormData){
                            var form = new FormData();
                            form.append('file[]', files[0]);
                            form.append('method', 'ajax');
                            form.append('actions', "upload-bundle");
                            xhr.send(form);
                        }else{
                            xhr.send(files[i]);
                        }
                    }
                }
            );
            
            function process_result(raw_data){
                var id = 'bootstrap-modal-manager-id-0';
                
                eval("var data = " + raw_data);
                //console.log(data);
                if (data['resolve-dependencies'] && data['resolve-dependencies']['status'] == 'error') {
                    
                    var options = {
                        title: 'Error processing bundle',
                        header: true
                    };
                    
                    var $message = $("<div><p><strong>The following errors occurred while processing the bundle:</strong><ul></ul></div>");
                    
                    for(var i = 0; i < data['resolve-dependencies']['errors'].length; i++){
                        $message.find('ul').append('<li>' + data['resolve-dependencies']['errors'][i] + '</li>');
                    }
                    
                    $('#' + id).modal('hide');
                    
                    
                    id = $('body').modal_manager('alert', $message, options);
                }else if (data['resolve-dependencies'] && data['resolve-dependencies']['status'] == 'success') {
                    //console.log('success');
                    //alert('success id:' + id);
                    $('#' + id).find('.message').empty().append(data['resolve-dependencies']['display']);
                    
                    if (data['resolve-dependencies']['next_action']){
                        $.ajax({
                            url: data['resolve-dependencies']['next_action'],
                            success: process_result
                        });
                    }
                    
                }else if (data['install-bundle'] && data['install-bundle']['status'] == 'success') {
                    //console.log('success');
                    //alert('success id:' + id);
                    $('#' + id).find('.message').empty().append(data['install-bundle']['display']);
                    
                    if (data['install-bundle']['next_action']){
                        $.ajax({
                            url: data['install-bundle']['next_action'],
                            success: process_result
                        });
                    }
                    
                }
            }
            
        }
    )
    
})(jQuery);