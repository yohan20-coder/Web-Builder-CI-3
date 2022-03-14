
<script src="<?= BASE_ASSET; ?>js/custom.js"></script>


<?= form_open('', [
    'name'    => 'form_form_test', 
    'class'   => 'form-horizontal form_form_test', 
    'id'      => 'form_form_test',
    'enctype' => 'multipart/form-data', 
    'method'  => 'POST'
]); ?>
 
<div class="form-group ">
    <label for="input" class="col-sm-2 control-label">Input 
    <i class="required">*</i>
    </label>
    <div class="col-sm-8">
        <input type="text" class="form-control" name="input" id="input" placeholder=""  >
        <small class="info help-block">
        </small>
    </div>
</div>
 
<div class="form-group ">
    <label for="textarea" class="col-sm-2 control-label">Textarea 
    <i class="required">*</i>
    </label>
    <div class="col-sm-8">
        <textarea id="textarea" name="textarea" rows="5" class="textarea form-control" ></textarea>
        <small class="info help-block">
        </small>
    </div>
</div>
 
<div class="form-group ">
    <label for="captcha" class="col-sm-2 control-label">Captcha 
    <i class="required">*</i></label>
    <div class="col-sm-8">
        <?php $cap = get_captcha(); ?>
        <div class="captcha-box"  data-captcha-time="<?= $cap['time']; ?>">
            <input type="text" name="captcha" placeholder="">
            <a class="btn btn-flat  refresh-captcha  "><i class="fa fa-refresh text-danger"></i></a>
            <span  class="box-image"><?= $cap['image']; ?></span>
        </div>
        <small class="info help-block">
        </small>
    </div>
</div>


<div class="row col-sm-12 message">
</div>
<div class="col-sm-2">
</div>
<div class="col-sm-8 padding-left-0">
    <button class="btn btn-flat btn-primary btn_save" id="btn_save" data-stype='stay'>
    Submit
    </button>
    <span class="loading loading-hide">
    <img src="http://cicool.internal.test:80/asset//img/loading-spin-primary.svg"> 
    <i>Loading, Submitting data</i>
    </span>
</div>
</form></div>


<!-- Page script -->
<script>
    $(document).ready(function(){
     $('.refresh-captcha').on('click', function(){
        var capparent = $(this);

        $.ajax({
            url: BASE_URL + '/captcha/reload/'+ capparent.parent('.captcha-box').attr('data-captcha-time'),
            dataType: 'JSON',
        })
        .done(function(res) {
            capparent.parent('.captcha-box').find('.box-image').html(res.image);
            capparent.parent('.captcha-box').attr('data-captcha-time', res.captcha.time);
        })
          .fail(function() {
            $('.message').printMessage({message : 'Error getting captcha', type : 'warning'});
          })
          .always(function() {
          });
        
     });
          $('.form-preview').submit(function(){
        return false;
     });

     $('input[type="checkbox"].flat-red').iCheck({
      checkboxClass: 'icheckbox_minimal-red',
      radioClass: 'iradio_minimal-red'
     });


    
      $('.btn_save').click(function(){
        $('.message').fadeOut();
            
        var form_form_test = $('#form_form_test');
        var data_post = form_form_test.serializeArray();
        var save_type = $(this).attr('data-stype');
    
        $('.loading').show();
    
        $.ajax({
          url: BASE_URL + 'form/form_test/submit',
          type: 'POST',
          dataType: 'json',
          data: data_post,
        })
        .done(function(res) {
          if(res.success) {
            
            if (save_type == 'back') {
              window.location.href = res.redirect;
              return;
            }
    
            $('.message').printMessage({message : res.message});
            $('.message').fadeIn();
            resetForm();
            $('.chosen option').prop('selected', false).trigger('chosen:updated');
                
          } else {
            $('.message').printMessage({message : res.message, type : 'warning'});
          }
    
        })
        .fail(function() {
          $('.message').printMessage({message : 'Error save data', type : 'warning'});
        })
        .always(function() {
          $('.loading').hide();
          $('html, body').animate({ scrollTop: $(document).height() }, 1000);
        });
    
        return false;
      }); /*end btn save*/


      
             
           
    }); /*end doc ready*/
</script>