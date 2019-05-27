<?php include 'header.php' ?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="page-head">
				<div class="page-title">
					<h1>Data SIAP 2017 <small>Teknoligi Informasi</small></h1>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
                    <div class="portlet box blue-madison">
                        <div class="portlet-title">
							<div class="caption">
                                <i class="fa fa-globe"></i>Upload Data SIAP 2017
							</div>
                        </div>
                        <div class="portlet-body">
                            <div class="form-group">
                                    <?php echo form_open_multipart('home/excel');?>
										<label for="exampleInputFile1">File input</label>
										<input type="file" name="file" id='files'>
                               
                                <br>
                                <div class="form-actions">
									<button type="submit" class="btn blue">Submit</button>
                                </div>
                                <br>
                                 <?php
                                            if($this->session->flashdata('pesan')==TRUE):
                                                echo'<div class="alert" role="alert">';
                                                echo $this->session->flashdata('pesan');
                                                echo '</div>';
                                            endif;
                                        ?>
                                
									<?php echo form_close();?>	
				            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
      var file = document.getElementById('files');
         file.onchange = function(e){
            var ext = this.value.match(/\.([^\.]+)$/)[1];
            switch(ext)
            {
                case 'xlsx':
                case 'csv':
                    //alert('allowed');
                    break;
                default:
                    alert('Maaf file bukan .xlsx');
                    this.value='';
            }
    };
</script>