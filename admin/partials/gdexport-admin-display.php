<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://seobox.io
 * @since      1.0.0
 *
 * @package    GDExport
 * @subpackage GDExport/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    <h2>GDExport</h2>

    <h1>
        <a class="button-primary" href="<?php echo $url ?>" target="_blank">Connect GDExport</a>
    </h1>
    <br>
    <table class="widefat">
        <thead>
        <tr>
            <th>WordPresss Username</th>
            <th>Email</th>
            <th>GDExport Key</th>
            <th>Connect User</th>
        </tr>
        </thead>
		<?php
		foreach ( $users as $user ) {
			if ( $user->version === 2 ) {
				continue;
			}
			echo "<tr>";
			echo "<td>" . get_userdata( $user->user_id )->user_login . "</td>";
			echo "<td>" . get_userdata( $user->user_id )->user_email . "</td>";
			echo "<td>" . $user->secret . "</td>";
			echo '<td><a class="button-primary" href="' . $this->gdexport_connect_user_url( $user ) . '" target="_blank"> Connect ' . get_userdata( $user->user_id )->user_login . '</a></td>';
			echo "</tr>";
		}
		?>
    </table>
</div>