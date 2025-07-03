<?php
/**
 * Centralized Data Tables for Auditing and Testing Saving.
 *
 * This file retrieves all data from a specified Formidable Forms entry and its
 * associated custom post. It now includes a form to test saving data back to
 * the custom post's meta fields.
 */

// Bootstrap WordPress
require_once( __DIR__ . '/../../../wp-load.php' );

// --- Globals ---
$entry_id = isset( $_GET['entry_id'] ) ? absint( $_GET['entry_id'] ) : 0;
$feedback_message = '';

// --- HANDLE FORM SUBMISSION (SAVING LOGIC) ---
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['save_test_data'] ) ) {
    // 1. Security Check: Verify nonce
    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_test_data_nonce' ) ) {
        die( 'Security check failed!' );
    }

    // 2. Sanitize Inputs
    $post_id_to_update = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $topic_data = isset( $_POST['generated_topic'] ) ? sanitize_textarea_field( $_POST['generated_topic'] ) : '';
    $questions_data = isset( $_POST['generated_questions'] ) ? sanitize_textarea_field( $_POST['generated_questions'] ) : '';

    // 3. Update Post Meta
    if ( $post_id_to_update ) {
        // Update the fields with the new data.
        // update_post_meta will create the field if it doesn't exist.
        update_post_meta( $post_id_to_update, 'generated_topic', $topic_data );
        update_post_meta( $post_id_to_update, 'generated_questions', $questions_data );

        $feedback_message = '<div class="notice notice-success">Test data saved successfully!</div>';
    } else {
        $feedback_message = '<div class="notice notice-error">Error: Invalid Post ID. Data not saved.</div>';
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralized Data Audit & Save</title>
    <style>
        body { font-family: sans-serif; margin: 2em; color: #333; background-color: #f9f9f9; }
        .container { max-width: 960px; margin: 0 auto; background: #fff; padding: 1em 2em; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #222; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 2em; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        code { background-color: #eee; padding: 2px 4px; border-radius: 4px; font-family: monospace; }
        .instructions { background-color: #f5f5f5; border-left: 5px solid #0073aa; padding: 1em 1.5em; margin-bottom: 2em;}
        .notice { padding: 1em; margin-bottom: 1.5em; border-left-width: 5px; border-left-style: solid; }
        .notice-success { background-color: #f0fff0; border-color: #28a745; }
        .notice-error { background-color: #fff0f0; border-color: #dc3545; }
        textarea { width: 100%; min-height: 120px; padding: 5px; }
        .button-primary { background-color: #0073aa; color: #fff; border: none; padding: 10px 15px; font-size: 16px; cursor: pointer; border-radius: 4px; }
        .button-primary:hover { background-color: #005f8a; }
    </style>
</head>
<body>
<div class="container">
<?php
// Display instructions and exit if no valid ID is provided.
if ( ! $entry_id ) :
?>
    <h1>Data Audit & Save Tool</h1>
    <div class="instructions">
        <p>Please provide a Formidable Forms entry ID to audit.</p>
        <p><strong>Example:</strong> <code><?php echo esc_url( get_home_url() ); ?>/path/to/your/plugin/centralized-data-tables.php?entry_id=123</code></p>
    </div>

<?php
else : // Valid Entry ID is present, proceed with data retrieval

    // Display feedback message if it exists
    if ( $feedback_message ) {
        echo $feedback_message;
    }

    // ---- Formidable Entry Data Retrieval ---
    if ( ! class_exists( 'FrmEntry' ) || ! class_exists( 'FrmField' ) ) {
        echo '<h1>Error: Formidable Forms is not active.</h1>';
        exit;
    }

    $entry = FrmEntry::getOne( $entry_id, true );

    if ( ! $entry ) {
        echo "<h1>Error: Formidable Entry with ID '{$entry_id}' not found.</h1>";
        exit;
    }

    $form_id = $entry->form_id;
    $form_fields = FrmField::get_all_for_form( $form_id );

    // --- Associated Post Data Retrieval ---
    $post_id = $entry->post_id;
    $post_meta = [];
    $post_data = null;
    $custom_post_type_name = 'guests'; // <-- Set your custom post type name here

    if ( $post_id ) {
        $post_data = get_post( $post_id );
        if ( $post_data && $post_data->post_type === $custom_post_type_name ) {
            $post_meta = get_post_meta( $post_id );
        } else {
            $post_data = null; // Post exists but is not the correct CPT
        }
    }
?>

    <h1>Centralized Data Audit for Formidable Entry #<?php echo esc_html( $entry_id ); ?></h1>

    <h2>Formidable Entry Details</h2>
    <table></table>

    <h2>Associated "Guests" Custom Post Details</h2>
    <?php if ( $post_data ) : ?>
        <p><strong>Post ID:</strong> <?php echo esc_html( $post_id ); ?></p>
        <p><strong>Post Title:</strong> <?php echo esc_html( $post_data->post_title ); ?></p>

        <div class="instructions">
            <h3>Test Saving to Post #<?php echo esc_html( $post_id ); ?></h3>
            <form method="POST" action="">
                <?php wp_nonce_field( 'save_test_data_nonce' ); // Security nonce ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">

                <p>
                    <label for="generated_topic"><strong>Field:</strong> <code>generated_topic</code></label><br>
                    <textarea id="generated_topic" name="generated_topic"></textarea>
                </p>
                <p>
                    <label for="generated_questions"><strong>Field:</strong> <code>generated_questions</code></label><br>
                    <textarea id="generated_questions" name="generated_questions"></textarea>
                </p>
                <p>
                    <button type="submit" name="save_test_data" class="button-primary">Save Test Data</button>
                </p>
            </form>
        </div>
        <h3>Current Post Meta Fields</h3>
        <table>
            <thead>
                <tr>
                    <th>Field Name (Meta Key)</th>
                    <th>Field Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $post_meta as $meta_key => $meta_values ) : ?>
                    <tr>
                        <td><code><?php echo esc_html( $meta_key ); ?></code></td>
                        <td>
                            <?php
                            foreach ( $meta_values as $meta_value ) {
                                $unserialized_value = maybe_unserialize( $meta_value );
                                if ( is_array( $unserialized_value ) || is_object( $unserialized_value ) ) {
                                    echo '<pre>' . esc_html( print_r( $unserialized_value, true ) ) . '</pre>';
                                } else {
                                    echo wp_kses_post( $unserialized_value ) . '<br>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No associated 'guests' custom post was found for this entry.</p>
    <?php endif; ?>

<?php endif; // End of the main if/else block ?>
</div>
</body>
</html>