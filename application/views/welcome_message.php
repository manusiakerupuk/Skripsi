<div class="col-md-8">
   <?php
    if($this->session->flashdata('pesan')==TRUE):
    echo'<div class="alert alert-success" role="alert">';
    echo $this->session->flashdata('pesan');
    echo "</div>";
    endif;?>
<?php echo form_open_multipart('home/excel');?>
<input type="file" name="file" size="20" id='files'/>
<br /><br />
<button type="submit" class="btn btn-primary">Update</button>
</form>
</div>
<script type="text/javascript">
      var file = document.getElementById('files');
         file.onchange = function(e){
            var ext = this.value.match(/\.([^\.]+)$/)[1];
            switch(ext)
            {
                case 'xlsx':
                    //alert('allowed');
                    break;
                default:
                    alert('Maaf file bukan .xlsx');
                    this.value='';
            }
    };
</script>