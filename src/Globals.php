<?php

namespace WCM\CurrentAdminInfo;

/**
 * @package    Current Admin Info
 * @subpackage Globals/Availability
 */
class Globals extends AbstractScreenData
{
	private $globals = array(
		'current_user',
		'hook_suffix',
		'menu',
		'page',
		'pagenow',
		'parent_file',
		'self',
		'submenu_file',
		'submenu',
		'taxnow',
		'typenow',
	);

	public function collect()
	{
		// Toggle script for Arrays/Objects
		if ( 'admin_init' === current_filter() )
			add_action( 'admin_print_footer_scripts', array( $this, 'toggle_script' ) );

		foreach ( $this->globals as $key => $var )
		{
			// Check availability in global context
			if ( ! empty( $GLOBALS[ $var ] ) )
			{
				$global = $GLOBALS[ $var ];

				// Object/Array handling
				! is_scalar( $global )
					AND $global = $this->scalar_helper( $global );

				// Add to result
				$this->data[ get_class() ][] = sprintf(
					"<td><var>$%s</var></td><td>%s</td><td>%s</td>",
					$var,
					$global,
					current_filter()
				);

				// Don't loop this one the next time
				unset( $this->globals[ $key ] );
			}

			// We're done: Remove callback & Return
			if (
				empty( $this->globals )
				OR 'contextual_help' === current_filter()
			)
				return remove_filter( current_filter(), __FUNCTION__ );
		}
	}

	/**
	 * class="widefat" NOT allowed in here, as it visually
	 * destroys! the Quick-Edit on Post screens.
	 * @param array $set
	 * @return string
	 */
	protected function markup( Array $set )
	{
		sort( $set );
		return sprintf(
			'<p>%s</p><table class="form-table">%s<tbody><tr>%s</tr></tbody></table>',
			'Filters, where globals are already available.',
			'<thead><tr><th>Name</th><th>Data</th><th>Hook</th></tr></thead>',
			implode( '</tr><tr>', $set )
		);
	}

	protected function scalar_helper( $global )
	{
		is_object( $global )
			AND $global = (array) $global;

		$global = var_export( $global, true );
		$html   = "<a href='#' class='cas-toggle'>Open</a><pre style='display:none;'>{$global}</pre>";

		return "<div class='current-screen-global'>{$html}</div>";
	}

	public function toggle_script()
	{
		?>
		<script type="text/javascript">
		jQuery( document ).ready( function($)
		{
			$( '#contextual-help-link' ).on( 'click', function( event )
			{
				$( '.cas-toggle' ).each( function( index, el )
				{
					$( el ).on( 'click', function( event )
					{
						event.preventDefault();
						var text = $( el ).text() === 'Open' ? 'Close' : 'Open';
						$( el ).text( text );
						$( el ).next( 'pre' ).toggle();
					} );
				} );
			} );
		} );
		</script>
		<?php
	}
}