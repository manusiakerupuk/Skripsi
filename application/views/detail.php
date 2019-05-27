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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box blue-madison">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Data SIAP 2017
							</div>
                            <div class="tools">
								<a href="javascript:;" class="reload">
								</a>
								<a href="javascript:;" class="remove">
								</a>
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_1">
								 <?php
                                foreach ($lihat as $b ) { ?>
							<thead>
							<tr>
								<th>
									 Judul
								</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>
									<?php echo $b->Judul; ?>
								</td>
							</tr>
							</tbody>
							<thead>
							<tr>
								<th>
									 Abstrak
								</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>
									<?php echo $b->Abstrak; ?>
								</td>
							</tr>
							</tbody>
								
                                <?php } ?>
							
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
<?php include "footer.php"?>