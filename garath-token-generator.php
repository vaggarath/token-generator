<?php
/**
 * Plugin Name: Garath's Token Generator
 * Plugin URI: http://animalia.univ-tours.fr
 * Description: Generateur de token selon la librairie JWT token PHP de firebase
 * Version: 0.1
 * Author: Chambon Julien
 * Author URI: http://vag.ovh/
 */

 ?>

 <?php
//If someone tries to access the plugin by it's direct url: rebuke him/her
if(!defined('ABSPATH')){
    echo "<div id='error'>This is not the droid you're looking for</div>";
    exit;
}

// require 'JwtHandler.php';


function token_plugin_page() {
	$page_title = 'Générateur de token JWT';
	$menu_title = 'Token Generator';
	$capatibily = 'manage_options';
	$slug = 'tokengen-plugin';
	$callback = 'tokengen_page_html';
	$icon = 'dashicons-admin-network';
	$position = 60;

	add_menu_page($page_title, $menu_title, $capatibily, $slug, $callback, $icon, $position);
}

add_action('admin_menu', 'token_plugin_page');

//data will register into the WP's options table
//les infos s'enregistreront dans la table "options" de wordpress

function tokengen_page_html() { ?>
    <style>
        table{
            max-width:100%;
        }

        td{
            word-break: break-word;
            margin:0 auto;
            align-items: center;
            align-content: center;
            text-align: center;
            
        }

        a{
            text-decoration: none;
        }

        #email{
            min-width:15rem;
        }

        table,
        td {
            border: 1px solid #333;
        }

        th{
            background-color: #333;
            color: #fff;
            text-align: center;
        }

        #trash{
            cursor:pointer;
        }

        #wpfooter{
            position:relative !important;
        }
    </style>
    <!-- Form to create and register a new token : It asks for an email but there isn't any check because some token might be attributed to groups of people -->
    <div class="wrap top-bar-wrapper">
        <script>
            copyTo = () =>{
                console.log("blah");
            }
        </script>
        <h1 class="text-center">Créer un token pour l'utilisation de l'API Animalia</h1>
        <form method="post" action="#">
            <?php settings_errors() ?>
            <!-- native WP's fonction -->
            <?php ""//settings_fields('tokengen_option_group'); ?>
            <label for="email_field_eat">Email du token à générer :</label>
            <input name="email_field" id="email_field_eat" type="text" placeholder="Indiquer l'adresse mail du nouveau token" style="width:300px;"> 
            <?php submit_button("Créer un nouveau token"); ?>
            <!-- native WP's fonction -->
        </form>
        <div class="wrap">
            <?php
                if(isset($_POST['submit']) && !empty($_POST['submit']) && !empty($_POST['email_field'])){
                    // var_dump($_POST);
                    $jwt = new JwtHandler();
                    $token = $jwt->_jwt_encode_data(
                        'http://animalia.univ-tours.fr/',
                        array("email"=>$_POST['email_field'],"key"=>0)
                    );

                    add_option( 'token:'.$_POST['email_field'], $token, '', 'yes' );
                    // WP's fonction that register the form and the created token into the options tablethe value is a string beggining by token: followed by the token
                    
                    ?>
                        <span>Le token pour l'email <strong><?= $_POST['email_field'] ?></strong> a bien été créé : <?= $token ?></span>
                    <?php
                }

                if(isset($_GET['deleteToken']) && !empty($_GET['deleteToken'])){
                    // var_dump($_GET['deleteToken']);
                    delete_option($_GET['deleteToken']);

                    $url = './?page=tokengen-plugin';

                    echo("<script>location.href = '".$url."'</script>");
                    
                }
            ?>
        </div>
        <div class="wrap">
            <?php
                $all_options = wp_load_alloptions();
                $my_options  = array();
                
                foreach ( $all_options as $name => $value ) {
                    if ( stristr( $name, 'token:' ) ) {
                        //we get all the entries inside options and only retrieve those starting with token:
                        $my_options[ $name ] = $value;
                    }
                }
                
                // var_dump( $my_options );
            ?>
            <h2>Token créés pour l'API :</h2>
            
                <?php
                    echo "<table><tr>";
                    echo "<th>Email</th><th>token</th><th>validité</th><th>Supprimer</th>";
                    echo "</tr>";
                    foreach ($my_options as $key=>$value) {
                        
                        echo "<tr>";
                        echo "<td style='word-break:normal;'  id='email'>" . substr($key, 6) . "</td>";
                        echo "<td>" . $value . "</td>";
                        echo "<td>". isValide($value) ."</td>";
                        // echo "<td><button onclick='copyTo('".$value."')'>Copier le token</button></td>";
                        echo '<td><a href="./?page=tokengen-plugin&deleteToken='.$key.'"><span class="dashicons dashicons-trash" id="trash"></span></a></td>';
                        echo "</tr>";
                      }
                      
                      echo "</table>";
                ?>

               
            
        </div>

    </div>
    
    <?php }

    function isValide($tok){
        $jwt = new JwtHandler();
        $data =  $jwt->_jwt_decode_data($tok);
        return $data ? '<span class="dashicons dashicons-saved" style="margin:0 auto; text-align: center; color:yellowgreen;"></span>' : '<span class="dashicons dashicons-saved" style="color:red;"></span>';
    }

    function userExist($name){
        $doesHe = username_exists($name) || email_exists($name) ? true : false;
        return $doesHe;
    }
    ?>
 