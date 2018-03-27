<div>
    <form action='options.php' method='post'>

		<h2><?php _e( 'Software Update Manger', 'softupdatemgr' ); ?></h2>

		<?php
		settings_fields( 'softupdatemgr_settings_group' );
		do_settings_sections( 'softupdatemgr_settings_group' );
		submit_button();
		?>

	</form>
</div>