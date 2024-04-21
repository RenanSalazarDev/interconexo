<?php // Silence is golden
function shortcode_oniros_estudiantes_inscritos() {
	ob_start();  

	// Loading WP_List_Table class file
	// We need to load it as it's not automatically loaded by WordPress
	if (!class_exists('WP_List_Table')) { require_once(ABSPATH . '/wp-admin/includes/class-wp-list-table.php'); }

	// Extending class
	class Supporthost_List_Table extends WP_List_Table
	{
		// define $table_data property
		private $table_data;

    	// Get table data
    	private function get_table_data( $search = '' ) {
		
			$args_pagos = array(
				'post_type' => 'preinscripciones',
				'posts_per_page' => 10,  // Obtener todas las publicaciones
				'meta_query' => array(
					'relation' => 'AND', 
					array(
						'key'     => 'preinscripcion__status',
						'value'   => 'Inscrito', 
						'compare' => '='
					),
					array(
						'key'     => 'preinscripcion__estado', 
						'value'   => 'Activa', 
						'compare' => '='
					),
				  )
			); 
		
	  	  	$customer_posts = get_posts($args_pagos);
      	  	$customers = array();

			foreach($customer_posts as $customer_post) {
				$new_customer = array();
				$new_customer['id_preinscripcion'] 	= get_post_meta($customer_post->ID, 'preinscriopcion__id', true);
				$new_customer['ci'] 				= get_post_meta($customer_post->ID, 'preinscripcion__cedula', true);
				$new_customer['nombre_apellido'] 	= get_post_meta($customer_post->ID, 'preinscripcion__nombre_apellido', true);
				$new_customer['correo'] 			= get_post_meta($customer_post->ID, 'preinscripcion__correo', true);
				if(get_post_meta($customer_post->ID, 'preinscripcion__periodo', true) == '')
					$new_customer['periodo'] 		= "No especificado aún";
				else
					$new_customer['periodo'] 		= get_post_meta($customer_post->ID, 'preinscripcion__periodo', true);
				$new_customer['programa'] 			= get_post_meta($customer_post->ID, 'preinscripcion__programa', true);
				$new_customer['detalles'] 			= "<a href='https://cea.ieacss.com/wp-admin/post.php?post=$customer_post->ID&action=edit' class='button butoon-primary'>Ver Detalle </a>";
				array_push($customers, $new_customer);
			}

      		return $customers;
    	}

    	// Define table columns
		function get_columns()
		{
			$columns = array(
					'cb'            	=> '<input type="checkbox" />',
					'id_preinscripcion' => __('ID Preinscripcion', 'supporthost-admin-table'),
					'ci'   				=> __('C.I/DNI/RUC o Pasaporte', 'supporthost-admin-table'),
					'nombre_apellido'  	=> __('Nombres y Apellidos', 'supporthost-admin-table'),
					'correo'  			=> __('Correo', 'supporthost-admin-table'),
					'periodo'       	=> __('Periodo Académico', 'supporthost-admin-table'),
					'programa'       	=> __('Programa o Diplomado', 'supporthost-admin-table'),
					'detalles'        	=> __('Detalles', 'supporthost-admin-table')
			);
			return $columns;
		}

    	// Bind table with columns, data and all
		function prepare_items()
		{
			//data
			if ( isset($_POST['s']) ) {
				$this->table_data = $this->get_table_data($_POST['s']);
			} else {
				$this->table_data = $this->get_table_data();
			}

			$columns = $this->get_columns();
			$hidden = ( is_array(get_user_meta( get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true)) ) ? get_user_meta( get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true) : array();
			$sortable = $this->get_sortable_columns();
			$primary  = 'id_cea';
			$this->_column_headers = array($columns, $hidden, $sortable, $primary);

			usort($this->table_data, array(&$this, 'usort_reorder'));

			/* pagination */
			$per_page = $this->get_items_per_page('elements_per_page', 10);
			$current_page = $this->get_pagenum();
			$total_items = count($this->table_data);

			$this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

			$this->set_pagination_args(array(
					'total_items' => $total_items, // total number of items
					'per_page'    => $per_page, // items to show on a page
					'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
			));
			
			$this->items = $this->table_data;
		}

		// set value for each column
		function column_default($item, $column_name)
		{
			switch ($column_name) {
				case 'id_preinscripcion':
				case 'ci':
				case 'nombre_apellido':
				case 'correo':
				case 'periodo':
				case 'programa':
				case 'detalles':
				default:
					return $item[$column_name];
			}
		}

		// Add a checkbox in the first column
		function column_cb($item)
		{
			return sprintf('<input type="checkbox" name="element[]" value="%s" />', $item['id'] );
		}

		// Define sortable column
		protected function get_sortable_columns()
		{
			$sortable_columns = array(
				'periodo'  => array('periodo', false),
				'nombre_apellido'  => array('nombre_apellido', false),
				'correo'  => array('correo', false),
				'programa'  => array('programa', false),
				'ci' => array('ci', false)
			);
			return $sortable_columns;
		}

		// Sorting function
		function usort_reorder($a, $b)
		{
			  $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
			  $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			  $result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
			  return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
			
		}

		// Adding action links to column
		function column_name($item)
		{
			$actions = array(
					'edit'      => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
					'delete'    => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Delete', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'delete', $item['ID']),
			);
			return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
		}

		// To show bulk action dropdown
		function get_bulk_actions()
		{
				$actions = array(
						'delete_all'    => __('Delete', 'supporthost-admin-table'),
						'draft_all' => __('Move to Draft', 'supporthost-admin-table')
				);
				return $actions;
		}
	}

	// Adding menu
	function my_add_menu_items() {
		/*Agregar Opción en el Menu de Coordinación Académica */
		add_submenu_page('users.php', 'Estudiantes Inscritos', 'Estudiantes Inscritos', 'manage_options', 'estudiantes-inscritos','supporthost_list_init');
		add_action('admin_menu', 'add_the_theme_page_estudiantes_aprobados');
	}
	add_action('admin_menu', 'my_add_menu_items');

	// add screen options
	function supporthost_sample_screen_options() {
		global $supporthost_sample_page;
		global $table;

		$screen = get_current_screen();
		// get out of here if we are not on our settings page
		if(!is_object($screen) || $screen->id != $supporthost_sample_page) return;

		$args = array(
			'label' => __('Elements per page', 'supporthost-admin-table'),
			'default' => 2,
			'option' => 'elements_per_page'
		);
		add_screen_option( 'per_page', $args );

		$table = new Supporthost_List_Table();
	}
	add_filter('set-screen-option', 'test_table_set_option', 10, 3);
	
	function test_table_set_option($status, $option, $value) {
	  return $value;
	}

	// Plugin menu callback function
	function supporthost_list_init()
	{
		  // Creating an instance
		  $table = new Supporthost_List_Table();

		  echo '<div class="wrap"><h2>Estudiantes Inscritos</h2>';
		  echo '<form method="post">';
		  // Prepare table
		  $table->prepare_items();
		  /* Search form
		  $table->search_box('search', 'search_id');*/
		  // Display table
		  $table->display();
		  echo '</div></form>'; ?>
		
		<form style="margin-top: 20px; margin-left:5px" id="form-1" action="<?php echo $_SERVER['PHP_SELF'].'?post_type=coordinacion&page=estudiantes-inscritos'; ?>" method="post">
			<input name="boreas__estudiantes" type="hidden" value="boreas__estudiantes_inscritos" id="boreas__estudiantes_inscritos">
			<button type="submit" id="SubmitButton_all" name="SubmitButton" class="button button-primary button-hero"><i class="fa-solid fa-file-arrow-down"></i> Descargar </button>
		</form>

		<h2 style="margin-top: 50px; margin-left:5px">Seleccione el Programa y el Periodo Académico para descargar el listado de estudiantes inscritos.</h2>
		<form style="margin-top: 20px; margin-left:5px" id="form-1" action="<?php echo $_SERVER['PHP_SELF'].'?post_type=coordinacion&page=estudiantes-inscritos'; ?>" method="post">
			<input name="boreas__estudiantes" type="hidden" value="boreas__estudiantes_inscritos_materia" id="boreas__estudiantes_inscritos-materia">
			
		 	<?php $posts_programas = get_posts(array(
				 'post_type' => 'programas',
				 'posts_per_page' => -1,  // Obtener todas las publicaciones
				 'meta_query' => array(
					 array(
						 'key' => ' estado__formaciones',
						 'value' => 'Activo',
						 'compare' => '=',
					 ),
				 )
			  )); ?>
			
			  <select name="oniros__programa" id="oniros__programa">
				  <option value ="todos">Todos los Programas</option>
				 <?php foreach( $posts_programas as $post ): ?>
					<option value = "<?php echo get_field('programa__formaciones', $post->ID); ?>"><?php echo get_field('programa__formaciones', $post->ID); ?></option>
				 <?php endforeach; ?>
			  </select>
			
			  <select name="oniros__periodo" id="oniros__periodo">
				    <option value ="2022-I">2022-I</option>
					<option value ="2022-II">2022-II</option>
					<option value ="2023-I">2023-I</option>
					<option value ="2023-II">2023-II</option>
					<option value ="2024-I">2024-I</option>
					<option value ="2024-II">2024-II</option>
					<option value ="2025-I">2025-I</option>
					<option value ="2025-II">2025-II</option>
			  </select>
			
			<button type="submit" id="SubmitButton_programa" name="SubmitButton" class="button button-primary button-hero"><i class="fa-solid fa-file-arrow-down"></i> Descargar </button>		</form>
	<?php }
 	return ob_get_clean();
}
add_shortcode('interconexo_oniros_estudiantes_inscritos', 'shortcode_oniros_estudiantes_inscritos');
?>
