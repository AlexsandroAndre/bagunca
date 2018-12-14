<?php $bhumi_get_options = bhumi_get_options(); ?>
	<div class="bhumi_header_breadcrumb_title">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1><?php if(is_home()){echo "";}else{the_title();} ?></h1>
					<?php if($bhumi_get_options['breadcrumb_show']): ?>
					<!-- BreadCrumb -->
	                	<?php if (function_exists('bhumi_breadcrumbs')) bhumi_breadcrumbs(); ?>
	                <!-- BreadCrumb -->
	            	<?php endif; ?>
				</div>
			</div>
		</div>
	</div>