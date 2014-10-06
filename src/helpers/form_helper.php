<?php  //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists( 'form_button' ) ) {
    /**
     * Returns a form button that executes a request to the application
     * @param string $value Text to display on button.
     * @param string $method 'post' or 'get'
     * @param string $task The controller path to request
     * @param ApplicationRegistry $config An instance of the ApplicationRegistry
     * @param string $controller Name of the controller to use, if not set then form_button uses the current controller.
     * @param array $args An array of arguments that will be passed as hidden elements.
     * @param string $confirm If set a confirmation alert containing $confirm will appear before executing the request.
     * @return type
     */
    function form_button($value, $method, $task, ApplicationRegistry $config, $controller=null, array $args = null, $confirm = false ) {     
        $request = Request::instance( 'Request' );     
        if( ! $controller )
        {                   
            $controller = $request->{$config->controller_trigger};
        }
        ob_start();
        ?>
        <form style="display:inline;" method="<?php echo $method ?>" <?php echo $confirm ? "onSubmit=\"javascript: return confirm('$confirm')\"" : '' ?> >
            <input type="hidden" name="<?php echo $config->method_trigger ?>" value="<?php echo $task ?>" />
            <input type="hidden" name="<?php echo $config->controller_trigger ?>" value="<?php echo $controller ?>" />
            <?php
            if( isset( $args) ) {
                foreach( $args as $key => $hiddenValue ) {
                    ?><input type="hidden" name="<?php echo $key ?>" value="<?php echo $hiddenValue ?>" /><?php
                }
            }
            if( $method == 'get' )
            {
                ?>
                <input type="hidden" name="option" value="<?= $request->option ?>" />
                <input type="hidden" name="id" value="<?= $request->id ?>" />
                <input type="hidden" name="Itemid" value="<?= $request->Itemid ?>" />
                <input type="hidden" name="task" value="<?= $request->task ?>" />
                <?php
            }
            ?>
            <input type="submit" style="padding: 0 1em 0 1em" name="submit" value="<?php echo $value ?>" />
        </form>
        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists( 'form_link_button' ) ) {
    /**
     * Returns a link that executes a request to the application when clicked.
     * @param string $value The html text for the link.
     * @param string $task The controller path to execute.
     * @param ApplicationRegistry $config An instance of the ApplicationRegistry
     * @param string $controller The controller to use, if not set form_link_button will use the current controller.
     * @param array $args An array of arguments to append to the query string.
     * @param string $confirm If set a confirmation alert containing $confirm will appear before executing the request.
     * @return type
     */
    function form_link_button($value, $task, ApplicationRegistry $config, $controller=null, array $args = null, $confirm = false ) {                
        $url = $config->thelink;                
        $argsStr = '';
        if ( isset( $args) ) {
           foreach( $args as $key => $arg ) {
               $argsStr .= "&$key=$arg";
           } 
        }
        if( ! $controller )
        {
            $request = Request::instance( 'Request' );            
            $controller = $request->{$config->controller_trigger};
        }        
        ob_start();
        ?>
        <a href="<?php echo $url . 
                '&' . $config->controller_trigger . '=' . $controller .
                '&' . $config->method_trigger . '=' . $task . $argsStr ?>" 
                <?php echo $confirm ? "onClick=\"javascript: return confirm('$confirm')\"" : '' ?>>
            <?php echo $value ?>
        </a>       
        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists( 'p' ) ) {  
    /**
     * Use to check if $var is set in the view. Useful for displaying results from a posted form.
     * @param mixed $var
     * @return $var or null
     */
    function p( &$var )
    {
        return isset( $var ) ? stripslashes( $var ) : null;
    }
}
 
if ( ! function_exists( 'selected' ) ) {
    /**
     * Helper function for checking if a checkbox or option is selected.
     * 
     * If $var is set and is equal to $value then it is selected.
     * @param string $var Value to check is set
     * @param string $value Value to check against.
     * @return null or string 'selected="selected"'
     */
    function selected( &$var, $value )
    {
        if( p( $var ) )
        {
            return ( $var == $value ) ? 'selected="selected"' : null;
        }
        return null;
    }
}
