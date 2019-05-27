<?php include 'header.php' ?>
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="page-head">
				<div class="page-title">
					<h1>Data SIAP 2017 <small>Teknoligi Informasi</small></h1>
				</div>
			</div>
			<div class="row">
				<div class="portlet light">
					<div class="portlet-title">
								<div class="caption font-green-haze">
									<i class="icon-settings font-green-haze"></i>
									<span class="caption-subject bold uppercase"> Insert Data</span>
								</div>
					</div>
					<div class="portlet-body form">
						<form role="form" action="<?php echo base_url().'index.php/home/action_insertdata'; ?>" method="post" class="form-horizontal">
							<div class="form-body">
								<div class="form-group form-md-line-input">
											<label class="col-md-2 control-label" for="form_control_1">Judul</label>
											<div class="col-md-10">
												<input type="text" class="form-control" name="Judul" id="form_control_1" placeholder="Judul">
												<div class="form-control-focus">
												</div>
											</div>
								</div>
								<div class="form-group form-md-line-input ">
											<label class="col-md-2 control-label" for="form_control_1">Abstrak</label>
											<div class="col-md-10">
												<textarea class="form-control" name="Abstrak" rows="11" placeholder="Enter more text"></textarea>
												<div class="form-control-focus">
												</div>
											</div>
								</div>

							</div>
							<div class="form-actions">
										<div class="row">
											<div class="col-md-offset-2 col-md-10">
												<button type="submit" class="btn blue">Submit</button>
											</div>
										</div>
									</div>

						</form>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>			
				


<?php include "footer.php"?>