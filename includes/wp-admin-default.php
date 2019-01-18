<?php
/**
 * Admin defaults
 *
 * Has functions customize the WordPress Admins
 *
 * @author   closemarketing
 * @category Functions
 * @package  Admin
 */



/**
 * Class for admin fields
 */
class CCA_WPAdmin {

	/**
	 * Construct of Class
	 */
	public function __construct() {
		add_action( 'add_attachment', array( $this, 'set_image_meta_upon_image_upload' ) );

		// Cleans permalink stop words.
		add_filter( 'name_save_pre', 'cmk_seo_slugs', 0 );
		add_action( 'wp_ajax_sample-permalink', 'remove_stop_words_ajax', 0 );

		//Customizes Admin
		add_action( 'admin_head', 'hide_menu' );
		add_action( 'wp_dashboard_setup', 'cmk_add_custom_dashboard_widget' ); // disable default dashboard widgets
		add_action( 'admin_menu', array( $this, 'disable_default_dashboard_widgets' ) );
	}


	/**
	 * Automatically set the image Title, Alt-Text, Caption & Description upon upload
	 *
	 * @param string $post_ID Post id of attachment.
	 * @return void
	 */
	public function set_image_meta_upon_image_upload( $post_ID ) {

		if ( wp_attachment_is_image( $post_ID ) ) {

			// Sanitize the title: remove hyphens, underscores & extra.
			$my_image_title = get_post( $post_ID )->post_title;

			// Remove spaces.
			$my_image_title = preg_replace( '%\s*[-_\s]+\s*%', ' ', $my_image_title );

			// Sanitize the title: capitalize first letter of every word (other letters lower case).
			$my_image_title = ucwords( strtolower( $my_image_title ) );
			$my_image_meta = array(
				'ID' => $post_ID,
				'post_title' => $my_image_title,
			);

			// Set the image Alt-Text.
			update_post_meta( $post_ID, '_wp_attachment_image_alt', $my_image_title );

			// Set the image meta (e.g. Title, Excerpt, Content).
			wp_update_post( $my_image_meta );
		}
	}

	function remove_stop_words_ajax( $data ) {
		$post_id   = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$post_name = isset($_POST['new_slug'])? $_POST['new_slug'] : null;
		$new_title = isset($_POST['new_title'])? $_POST['new_title'] : null;
		$new_title = sanitize_title($new_title);
		$seo_slug  = strtolower(stripslashes($new_title));

		$seo_slug = preg_replace('/&.+?;/', '', $seo_slug); // Kill HTML entities
		$seo_slug_with_stopwords = $seo_slug;
		$seo_language = strtolower( substr( get_bloginfo ( 'language' ), 0, 2 ) ); 	// Check the language; we only want the first two letters
		if ( $seo_language == 'en' ) { // Check if blog language is English (en)
			$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_en()); // Turn it to an array and strip common/stop word by comparing against ENGLISH array
			$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
		} elseif ( $seo_language == 'es' ) { // Check if blog language is Spanish (es)
			$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_es()); // Turn it to an array and strip common/stop word by comparing against SPANISH array
			$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
		} elseif ( $seo_language == 'de' ) { // Check if blog language is German (de)
			$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_de()); // Turn it to an array and strip common/stop word by comparing against GERMAN array
			$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
		} elseif ( $seo_language == 'fr' ) { // Check if blog language is German (de)
			$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_fr()); // Turn it to an array and strip common/stop word by comparing against GERMAN array
			$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
		}
		$seo_slug = preg_replace ("/[^a-zA-Z0-9 \']-/", "", $seo_slug); // Kill anything that is not a letter, digit, space or apostrophe
		// Turn it to an array to count left words. If less than 3 words left, use original slug.
		// $clean_slug_array = explode( '-', $seo_slug );
		// if ( count( $clean_slug_array ) < 3 ) {
		//		$seo_slug = $seo_slug_with_stopwords;
		// }
		if (empty($post_name)) { $_POST['new_slug'] = $seo_slug; } // We don't want to change an existing slug
	}


	function cmk_seo_slugs( $slug ) {
		if ($slug) return $slug; // We don't want to change an existing slug
		global $wpdb;
		if ( !empty($_POST['post_title']) ) {
			$seo_slug = strtolower(stripslashes($_POST['post_title']));
			$seo_slug = preg_replace('/&.+?;/', '', $seo_slug); // Kill HTML entities
			$seo_slug_with_stopwords = $seo_slug;
			$seo_language = strtolower( substr( get_bloginfo ( 'language' ), 0, 2 ) ); 	// Check the language; we only want the first two letters
			if ( $seo_language == 'en' ) { // Check if blog language is English (en)
				$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_en()); // Turn it to an array and strip common/stop word by comparing against ENGLISH array
				$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
			} elseif ( $seo_language == 'es' ) { // Check if blog language is Spanish (es)
				$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_es()); // Turn it to an array and strip common/stop word by comparing against SPANISH array
				$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
			} elseif ( $seo_language == 'de' ) { // Check if blog language is German (de)
				$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_de()); // Turn it to an array and strip common/stop word by comparing against GERMAN array
				$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
			} elseif ( $seo_language == 'fr' ) { // Check if blog language is German (de)
				$seo_slug_array = array_diff (explode(" ", $seo_slug), cmk_seo_slugs_stop_words_fr()); // Turn it to an array and strip common/stop word by comparing against GERMAN array
				$seo_slug = join("-", $seo_slug_array);	// Turn the sanitized array into a string
			}
			$seo_slug = preg_replace ("/[^a-zA-Z0-9 \']-/", "", $seo_slug); // Kill anything that is not a letter, digit, space or apostrophe
			// Turn it to an array to count left words. If less than 3 words left, use original slug.
			// $clean_slug_array = explode( '-', $seo_slug );
			// if ( count( $clean_slug_array ) < 3 ) {
			//		$seo_slug = $seo_slug_with_stopwords;
			// }
			return $seo_slug;
	}
	}

function cmk_seo_slugs_stop_words_en () {
	   return array ("a", "able", "about", "above", "abroad", "according", "accordingly", "across", "actually", "adj", "after", "afterwards", "again", "against", "ago", "ahead", "ain't", "all", "allow", "allows", "almost", "alone", "along", "alongside", "already", "also", "although", "always", "am", "amid", "amidst", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren't", "around", "as", "a's", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully", "b", "back", "backward", "backwards", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "begin", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "but", "by", "c", "came", "can", "cannot", "cant", "can't", "caption", "cause", "causes", "certain", "certainly", "changes", "clearly", "c'mon", "co", "co.", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't", "course", "c's", "currently", "d", "dare", "daren't", "definitely", "described", "despite", "did", "didn't", "different", "directly", "do", "does", "doesn't", "doing", "done", "don't", "down", "downwards", "during", "e", "each", "edu", "eg", "eight", "eighty", "either", "else", "elsewhere", "end", "ending", "enough", "entirely", "especially", "et", "etc", "even", "ever", "evermore", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "f", "fairly", "far", "farther", "few", "fewer", "fifth", "first", "five", "followed", "following", "follows", "for", "forever", "former", "formerly", "forth", "forward", "found", "four", "from", "further", "furthermore", "g", "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten", "greetings", "h", "had", "hadn't", "half", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "hello", "help", "hence", "her", "here", "hereafter", "hereby", "herein", "here's", "hereupon", "hers", "herself", "he's", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "hundred", "i", "i'd", "ie", "if", "ignored", "i'll", "i'm", "immediate", "in", "inasmuch", "inc", "inc.", "indeed", "indicate", "indicated", "indicates", "inner", "inside", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'd", "it'll", "its", "it's", "itself", "i've", "j", "just", "k", "keep", "keeps", "kept", "know", "known", "knows", "l", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "let's", "like", "liked", "likely", "likewise", "little", "look", "looking", "looks", "low", "lower", "ltd", "m", "made", "mainly", "make", "makes", "many", "may", "maybe", "mayn't", "me", "mean", "meantime", "meanwhile", "merely", "might", "mightn't", "mine", "minus", "miss", "more", "moreover", "most", "mostly", "mr", "mrs", "much", "must", "mustn't", "my", "myself", "n", "name", "namely", "nd", "near", "nearly", "necessary", "need", "needn't", "needs", "neither", "never", "neverf", "neverless", "nevertheless", "new", "next", "nine", "ninety", "no", "nobody", "non", "none", "nonetheless", "noone", "no-one", "nor", "normally", "not", "nothing", "notwithstanding", "novel", "now", "nowhere", "o", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one", "ones", "one's", "only", "onto", "opposite", "or", "other", "others", "otherwise", "ought", "oughtn't", "our", "ours", "ourselves", "out", "outside", "over", "overall", "own", "p", "particular", "particularly", "past", "per", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provided", "provides", "q", "que", "quite", "qv", "r", "rather", "rd", "re", "really", "reasonably", "recent", "recently", "regarding", "regardless", "regards", "relatively", "respectively", "right", "round", "s", "said", "same", "saw", "say", "saying", "says", "second", "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't", "since", "six", "so", "some", "somebody", "someday", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure", "t", "take", "taken", "taking", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that'll", "thats", "that's", "that've", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "there'd", "therefore", "therein", "there'll", "there're", "theres", "there's", "thereupon", "there've", "these", "they", "they'd", "they'll", "they're", "they've", "thing", "things", "think", "third", "thirty", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "till", "to", "together", "too", "took", "toward", "towards", "tried", "tries", "truly", "try", "trying", "t's", "twice", "two", "u", "un", "under", "underneath", "undoing", "unfortunately", "unless", "unlike", "unlikely", "until", "unto", "up", "upon", "upwards", "us", "use", "used", "useful", "uses", "using", "usually", "v", "value", "various", "versus", "very", "via", "viz", "vs", "w", "want", "wants", "was", "wasn't", "way", "we", "we'd", "welcome", "well", "we'll", "went", "were", "we're", "weren't", "we've", "what", "whatever", "what'll", "what's", "what've", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "where's", "whereupon", "wherever", "whether", "which", "whichever", "while", "whilst", "whither", "who", "who'd", "whoever", "whole", "who'll", "whom", "whomever", "who's", "whose", "why", "will", "willing", "wish", "with", "within", "without", "wonder", "won't", "would", "wouldn't", "x", "y", "yes", "yet", "you", "you'd", "you'll", "your", "you're", "yours", "yourself", "yourselves", "you've", "z", "zero");
}
function cmk_seo_slugs_stop_words_es () {
	   return array ("a", "algún", "alguna", "algunas", "alguno", "algunos", "ambos", "ampleamos", "ante", "antes", "aquel", "aquellas", "aquellos", "aqui", "arriba", "atras", "b", "bajo", "bastante", "bien", "c", "cada", "cierta", "ciertas", "ciertos", "como", "con", "conseguimos", "conseguir", "consigo", "consigue", "consiguen", "consigues", "cual", "cuando", "de", "dentro", "donde", "dos", "e", "el", "ellas", "ellos", "empleais", "emplean", "emplear", "empleas", "empleo", "en", "encima", "entonces", "entre", "era", "eramos", "eran", "eras", "eres", "es", "esta", "estaba", "estado", "estais", "estamos", "estan", "estoy", "f", "fin", "fue", "fueron", "fui", "fuimos", "g", "gueno", "h", "ha", "hace", "haceis", "hacemos", "hacen", "hacer", "haces", "hago", "i", "incluso", "intenta", "intentais", "intentamos", "intentan", "intentar", "intentas", "intento", "ir", "j", "k", "l", "la", "largo", "las", "lo", "los", "m", "mientras", "mio", "modo", "muchos", "muy", "n", "nos", "nosotros", "o", "otro", "p", "para", "pero", "podeis", "podemos", "poder", "podria", "podriais", "podriamos", "podrian", "podrias", "por qué", "por", "porque", "primero desde", "puede", "pueden", "puedo", "que", "quien", "r", "s", "sabe", "sabeis", "sabemos", "saben", "saber", "sabes", "se", "ser", "si", "siendo", "sin", "sobre", "sois", "solamente", "solo", "somos", "soy", "su", "sus", "t", "también", "teneis", "tenemos", "tener", "tengo", "tiempo", "tiene", "tienen", "todo", "trabaja", "trabajais", "trabajamos", "trabajan", "trabajar", "trabajas", "trabajo", "tras", "tuyo", "u", "ultimo", "un", "una", "unas", "uno", "unos", "usa", "usais", "usamos", "usan", "usar", "usas", "uso", "v", "va", "vais", "valor", "vamos", "van", "vaya", "verdad", "verdadera cierto", "verdadero", "vosotras", "vosotros", "voy", "w", "x", "y", "yo", "z");
} // Stop word list from: http://www.ranks.nl/stopwords/spanish
function cmk_seo_slugs_stop_words_de () {
	   return array (
"a", "aber", "als", "am", "an", "auch", "auf", "aus", "b", "bei", "bin", "bis", "bist", "c", "d", "da", "dadurch", "daher", "darum", "das", "daß", "dass", "dein", "deine", "dem", "den", "der", "des", "deshalb", "dessen", "die", "dies", "dieser", "dieses", "doch", "dort", "du", "durch", "e", "ein", "eine", "einem", "einen", "einer", "eines", "er", "es", "euer", "eure", "f", "für", "g", "h", "hatte", "hatten", "hattest", "hattet", "hier hinter", "i", "ich", "ihr", "ihre", "im", "in", "ist", "j", "ja", "jede", "jedem", "jeden", "jeder", "jedes", "jener", "jenes", "jetzt", "k", "kann", "kannst", "können", "könnt", "l", "m", "machen", "mein", "meine", "mit", "muß", "müssen", "mußt", "musst", "müßt", "n", "nach", "nachdem", "nein", "nicht", "nun", "o", "oder", "p", "q", "r", "s", "seid", "sein", "seine", "sich", "sie", "sind", "soll", "sollen", "sollst", "sollt", "sonst", "soweit", "sowie", "t", "u", "über", "und", "unser unsere", "unter", "v", "vom", "von", "vor", "w", "wann", "warum", "was", "weiter", "weitere", "wenn", "wer", "werde", "werden", "werdet", "weshalb", "wie", "wieder", "wieso", "wir", "wird", "wirst", "wo", "woher", "wohin", "x", "y", "z", "zu", "zum", "zur");
} // Stop word list from: http://www.ranks.nl/stopwords/german
	function cmk_seo_slugs_stop_words_fr () {
	   	return array (
		"alors", "au", "aucuns", "aussi", "autre", "avant", "avec", "avoir", "bon", "car", "ce", "cela", "ces", "ceux", "chaque", "ci", "comme", "comment", "dans", "des", "du", "dedans", "dehors", "depuis", "deux", "devrait", "doit", "donc", "dos", "droite", "début", "elle", "elles", "en", "encore", "essai", "est", "et", "eu", "fait", "faites", "fois", "font", "force", "haut", "hors", "ici", "il", "ils", "je juste", "la", "le", "les", "leur", "là", "ma", "maintenant", "mais", "mes", "mine", "moins", "mon", "mot", "même", "ni", "nommés", "notre", "nous", "nouveaux", "ou", "où", "par", "parce", "parole", "pas", "personnes", "peut", "peu", "pièce", "plupart", "pour", "pourquoi", "quand", "que", "quel", "quelle", "quelles", "quels", "qui", "sa", "sans", "ses", "seulement", "si", "sien", "son", "sont", "sous", "soyez sujet", "sur", "ta", "tandis", "tellement", "tels", "tes", "ton", "tous", "tout", "trop", "très", "tu", "valeur", "voie", "voient", "vont", "votre", "vous", "vu", "ça", "étaient", "état", "étions", "été", "être");
	} // Stop word list from: http://www.ranks.nl/stopwords/french

	/**
	 * Customizes the dashboard
	 *
	 * @return void
	 */
	function cmk_custom_dashboard_widget() {
		echo '<p>Contacto: <strong>858 958 383</strong>. <a href="mailto:info@closemarketing.es" target="_blank">Correo</a> | <a href="https://www.closemarketing.es/ayuda/" target="_blank">Tutoriales y ayuda</a> | <a href="https://www.facebook.com/closemarketing" target="_blank">Facebook</a></p>';
	}

	function hide_menu() {
		$role_object = get_role( 'editor' );
		$role_object->add_cap( 'edit_theme_options' );

		if ( current_user_can( 'editor' ) ) {

			remove_submenu_page( 'themes.php', 'themes.php' ); // hide the theme selection submenu
			remove_submenu_page( 'themes.php', 'widgets.php' ); // hide the widgets submenu
			remove_submenu_page( 'themes.php', 'customize.php?return=%2Fwp-admin%2Ftools.php' ); // hide the customizer submenu
			remove_submenu_page( 'themes.php', 'customize.php?return=%2Fwp-admin%2Ftools.php&#038;autofocus%5Bcontrol%5D=background_image' ); // hide the background submenu

			// these are theme-specific. Can have other names or simply not exist in your current theme.
			remove_submenu_page( 'themes.php', 'yiw_panel' );
		}
	}

  

	function cmk_add_custom_dashboard_widget() {
		wp_add_dashboard_widget( 'cmk_custom_dashboard_widget', 'Contactar con Closemarketing', 'cmk_custom_dashboard_widget' );
	}


function disable_default_dashboard_widgets() {
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' ); // disable Simple:Press dashboard widget
	remove_meta_box( 'sf_announce', 'dashboard', 'normal' );
}

add_filter( 'admin_footer_text', 'cmk_remove_footer_admin' );
function cmk_remove_footer_admin() {
	echo 'Closemarketing - Dise&ntilde;o y Marketing 2017. Realizado sobre Gestor Contenidos WordPress.';
}

// Disables image link
function wpb_imagelink_setup() {
	$image_set = get_option( 'image_default_link_type' );

	if ( $image_set !== 'none' ) {
		update_option( 'image_default_link_type', 'none' );
	}
}
add_action( 'admin_init', 'wpb_imagelink_setup', 10 );

// Customize TinyMCE
function cmk_change_mce_options( $init ) {
	$init['block_formats']          = 'Párrafo=p;Título 2=h2;Título 3=h3;Título 4=h4;Título 5=h5';
	$init['theme_advanced_disable'] = 'forecolor';
	return $init;
}
add_filter( 'tiny_mce_before_init', 'cmk_change_mce_options' );

// Configurar campos Author
add_filter( 'user_contactmethods', 'cmk_remove_profile_fields', 10, 1 );
function cmk_remove_profile_fields( $contactmethods ) {
	// Añade Twitter
	$contactmethods['twitter'] = 'Twitter';
	// Añade Facebook
	$contactmethods['facebook']           = 'Facebook';
	$contactmethods['googleprofile']      = 'Google Profile URL';
	$contactmethods['googleprofileemail'] = 'Google Profile Email';
	unset( $contactmethods['aim'] );
	unset( $contactmethods['jabber'] );
	unset( $contactmethods['yim'] );
	return $contactmethods;
}
// cambiar logo administración y entrada
add_action( 'login_head', 'cmk_custom_login_logo' );
function cmk_custom_login_logo() {
	echo '<style type="text/css">
	h1 a { background-image:url(' . trailingslashit( plugin_dir_url( __FILE__ ) ) . '/logo-login.png) !important; }
	p.galogin-powered {display: none;}
	body.login {background: #85bb41 url(' . trailingslashit( plugin_dir_url( __FILE__ ) ) . 'login-background.gif) bottom left no-repeat; }
	.login label {color:#395912;}
	.login form {background: #b7da86;}
	.wp-core-ui .button-primary {background-color: #395912; border-color: none;}
	.wp-core-ui .button-primary.focus, .wp-core-ui .button-primary.hover, .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover {  background-color: #38710c;border-color: none; }
	form#loginform p.galogin { background: none repeat scroll 0 0 #395912; }
	.login #backtoblog a, .login #nav a { color: white; }
	.login #backtoblog a:hover, .login #nav a:hover { color: #395912; }
    </style>';
}

// * Remove accents in filenames
function cmk_sanitize_filename_on_upload( $filename ) {
	$ext = explode( '.', $filename );
	$ext = end( $ext );
	// Reemplazar todos los caracteres extranos.
	$sanitized = preg_replace( '/[^a-zA-Z0-9-_.]/', '', substr( $filename, 0, -( strlen( $ext ) + 1 ) ) );
	// Replace dots inside filename
	$sanitized = str_replace( '.', '-', $sanitized );
	return strtolower( $sanitized . '.' . $ext );
}
add_filter( 'sanitize_file_name', 'cmk_sanitize_filename_on_upload', 10 );

// * Change WordPress Admin bar color
add_action( 'wp_head', 'cmk_change_bar_color' );
add_action( 'admin_head', 'cmk_change_bar_color' );
function cmk_change_bar_color() {

	if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
		$color = 'red'; // local
	} else {
		$tldcal = explode( '.', $_SERVER['HTTP_HOST'] );
		$tld    = end( $tldcal );
		if ( $tld == 'loc' || $tld == 'dev' ) {
			$color = 'red';
		}
		// local
		else {
			$color = '#85b83c';
		}
		// live
	}
	?>
  <style>
  #wpadminbar{
  background: <?php echo $color; ?> !important;
  }
  #adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow, #adminmenu .wp-menu-arrow div, #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, .folded #adminmenu li.wp-has-current-submenu {
	background: <?php echo $color; ?> !important;
  }
  #adminmenu .wp-submenu a:focus, #adminmenu .wp-submenu a:hover, #adminmenu a:hover, #adminmenu li.menu-top>a:focus,#adminmenu li.menu-top:hover, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top:focus, #adminmenu li a:focus div.wp-menu-image:before, #adminmenu li.opensub div.wp-menu-image:before, #adminmenu li:hover div.wp-menu-image:before {
	color: <?php echo $color; ?> !important;
  }
  </style>
	<?php
}

// Disable menu Editor
add_action( '_admin_menu', 'cmk_remove_editor_menu', 1 );
function cmk_remove_editor_menu() {
	remove_action( 'admin_menu', '_add_themes_utility_last', 101 );
}

// Sencillez en la elaboración de artículos
add_action( 'admin_init', 'cmk_customize_meta_boxes' );
function cmk_customize_meta_boxes() {
	$current_user = wp_get_current_user();
	// if current user level is less than 3, remove the postcustom meta box
	if ( $current_user->user_level < 3 ) {
		remove_meta_box( 'postcustom', 'post', 'normal' );
	}

	remove_meta_box( 'trackbacksdiv', 'post', 'normal' );
}

add_action( 'wp_dashboard_setup', 'cmk_dashboard_widgets' );
function cmk_dashboard_widgets() {
	global $wp_meta_boxes;
	// remove unnecessary widgets
	// var_dump( $wp_meta_boxes['dashboard'] ); // use to get all the widget IDs
	unset(
		$wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'],
		$wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'],
		$wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']
	);
	// add a custom dashboard widget
	wp_add_dashboard_widget( 'dashboard_custom_feed', 'Noticias de Closemarketing', 'cmk_dashboard_custom_feed_output' ); // add new RSS feed output
}

add_filter( 'the_excerpt_rss', 'cmk_rss_post_thumbnail' );
function cmk_dashboard_custom_feed_output() {
	echo '<div class="rss-widget">';
	wp_widget_rss_output(
		array(
			'url'          => 'http://feeds.feedburner.com/closemarketing',
			'title'        => __( 'Closemarketing News', 'closemarketing-custom-admin' ),
			'items'        => 2,
			'show_summary' => 1,
			'show_author'  => 0,
			'show_date'    => 1,
		)
	);
	echo '</div>';
}

add_filter( 'the_content_feed', 'cmk_rss_post_thumbnail' );
function cmk_rss_post_thumbnail( $content ) {
	global $post;
	if ( has_post_thumbnail( $post->ID ) ) {
		$content = '<p>' . get_the_post_thumbnail( $post->ID ) .
		'</p>' . get_the_excerpt();
	}

	return $content;
}

// distinto color segun estado de entrada
add_action( 'admin_footer', 'cmk_posts_status_color' );
function cmk_posts_status_color() {
	?>
  <style>
  .status-draft { background: #FCE3F2 !important; }
  .status-pending { background: #87C5D6 !important; }
  .status-publish { /* por defecto */ }
  .status-future { background: #C6EBF5 !important; }
  .status-private { background: #F2D46F; }
  </style>
	<?php
}

// Imagenes Miniatura
if ( function_exists( 'add_theme_support' ) ) {
	add_filter( 'manage_posts_columns', 'cmk_postsColumns', 5 );
	add_filter( 'manage_pages_columns', 'cmk_postsColumns', 5 );

	add_action( 'manage_posts_custom_column', 'cmk_postsCustomColumn', 5, 2 );
	add_action( 'manage_pages_custom_column', 'cmk_postsCustomColumn', 5, 2 );
}
function cmk_postsColumns( $columns ) {
	$columns['dj_post_thumbnail'] = __( 'Thumbnail', 'closemarketing-custom-admin' );
	return ( $columns );
}
function cmk_postsCustomColumn( $column_name, $id ) {
	if ( $column_name === 'dj_post_thumbnail' ) {
		echo the_post_thumbnail( array( 125, 80 ) );
	}

}

/**
 * Adding Custom post type counts in 'Right now' Dashboard widget.
 * Acording this changes :
 * - https://core.trac.wordpress.org/ticket/26571
 * - https://core.trac.wordpress.org/ticket/26495
 * now you can't use 'right_now_*' action API to show your custom post type count from your Dashboard.
 * But if you running WP 3.8 or above, you can use 'dashboard_glance_items' instead.
 *
 * @package     WordPress
 * @subpackage  Hooks
 * @author      Fery Wardiyanto <ferywardiyanto@gmail.com>
 * @link        http://feryardiant.github.com
 * @version     1.0
 */

// Add custom post types count action to WP Dashboard
add_action( 'dashboard_glance_items', 'cmk_custom_posttype_glance_items' );

// Showing all custom posts count
function cmk_custom_posttype_glance_items() {
	$glances = array();

	$args = array(
		'public'   => true, // Showing public post types only
		'_builtin' => false, // Except the build-in wp post types (page, post, attachments)
	);

	// Getting your custom post types
	$post_types = get_post_types( $args, 'object', 'and' );

	foreach ( $post_types as $post_type ) {
		// Counting each post
		$num_posts = wp_count_posts( $post_type->name );

		// Number format
		$num = number_format_i18n( $num_posts->publish );
		// Text format
		$text = _n( $post_type->labels->singular_name, $post_type->labels->name, intval( $num_posts->publish ) );

		// If use capable to edit the post type
		if ( current_user_can( 'edit_posts' ) ) {
			// Show with link
			$glance = '<a class="' . $post_type->name . '-count" href="' . admin_url( 'edit.php?post_type=' . $post_type->name ) . '">' . $num . ' ' . $text . '</a>';
		} else {
			// Show without link
			$glance = '<span class="' . $post_type->name . '-count">' . $num . ' ' . $text . '</span>';
		}

		// Save in array
		$glances[] = $glance;
	}

	// return them
	return $glances;
}

}

new CCA_WPAdmin();
