<?php 
	$CurrentQuery = get_queried_object();
	$CurrentTaxID = '';
	
	switch ( get_class($CurrentQuery) ) {
		case 'WP_Term':
			$CurrentTaxID = array();
			$CurrentTaxID[] = $CurrentQuery->term_id;
			break;
		case 'WP_Post':
			$cpt = wp_get_post_terms($CurrentQuery->ID, 'azp_category');
			if($cpt){
				# search of children
				$CurrentTaxID = array();
				foreach ($cpt as $key => $value) {
					if($value->parent != 0){
						if(empty($CurrentTaxID)){
							$CurrentTaxID[] = $value->parent;
							$CurrentTaxID[] = $value->term_id;
						}
					}
				}

				if( empty($CurrentTaxID) )
					$CurrentTaxID[] = $cpt[0]->term_id;

			}

			break;
	}

	$az_prod_cal = get_terms( array(
		'taxonomy' => 'azp_category',
		'hide_empty' => false,
		'update_term_meta_cache' => true,
		'orderby'       => 'id', 
		'order'         => 'ASC',
		'parent'		=> '0',
	));
	
	$wer = get_the_id();

	echo '<div class="aside_box">';
	foreach ($az_prod_cal as $key => $value) {

		$subcat = get_terms( array(
			'taxonomy' 		=> 'azp_category',
			'hide_empty'	=> true,
			'update_term_meta_cache' => true,
			'orderby'       => 'id', 
			'order'         => 'ASC',
			'parent'		=> $value->term_id,

		));


		if ( empty($subcat) ){

			$TaxProducts = get_posts(array(
				'numberposts'	=> -1,
				'post_type'		=> 'az_products',
				'tax_query' => array(
					array(
						'taxonomy' => 'azp_category',
						'field'    => 'id',
						'terms'    => $value->term_id
					)
				)
			));

			$open = '';
			if($CurrentTaxID){
				if( in_array($value->term_id, $CurrentTaxID) )	$open = 'open';
			}else{
				if($key === 0)	$open = 'open';
			}


			?>
			<div class="aside_tax_box <?php echo $open ?>">
				<h2 class="tax_box_title">
					<a href="#" class="js-open-tax_list"><span class="tbt_ico"></span><?php echo get_field('abc','azp_category_'.$value->term_id); ?></a>
					<span class="aside_tax_name"><a href="<?php echo get_term_link( $value->term_id,'azp_category' ) ?>"><?php echo $value->name ?></a></span>
				</h2>
				<ul class="tax_product_list">
					<?php if($TaxProducts){
						foreach ($TaxProducts as $key => $tp){
							$current = '';
							if ( $tp->ID == $wer && get_class($CurrentQuery) == 'WP_Post') 
								$current = 'current';

echo '<li class="'.$current.'"><a href="'.get_permalink($tp->ID).'">'.remove_dackfaces($tp->post_title).'</a></li>';
						}
					}

					?>
				</ul>
			</div>
			<?php 

		} /* if subcat is empty */
		else {

			$open = '';
					if($CurrentTaxID){
						if( in_array($value->term_id, $CurrentTaxID) )	$open = 'open';
					}else{
						if($key === 0)	$open = 'open';
					}
			?>

		<div class="aside_tax_box <?php echo $open ?>">
			<h2 class="tax_box_title">
				<a href="#" class="js-open-tax_list"><span class="tbt_ico"></span><?php echo get_field('abc','azp_category_'.$value->term_id); ?></a>
				<span class="aside_tax_name"><a href="<?php echo get_term_link( $value->term_id,'azp_category' ) ?>"><?php echo $value->name ?></a></span>
			</h2>
			
			<ul class="tax_product_list has_sub">

			<?php 
			foreach ($subcat as $s_key => $s_value) {
			
				$TaxProducts = get_posts(array(
					'numberposts'	=> -1,
					'post_type'		=> 'az_products',
					'tax_query' => array(
						array(
							'taxonomy' => 'azp_category',
							'field'    => 'id',
							'terms'    => $s_value->term_id
						)
					)
				));
					$open = '';
					if($CurrentTaxID){
						if( in_array($s_value->term_id, $CurrentTaxID) )	$open = 'open';
					}else{
						if($s_key === 0)	$open = 'open';
					}

				?>
				<li class="subcat_box <?php echo $open ?> ">
					<h4 class="tax_box_title">
						<a href="#" class="js-open-tax_sublist"><span class="tbt_ico"></span><?php echo get_field('abc','azp_category_'.$s_value->term_id); ?></a>
						<!-- <span class="aside_tax_name"><a href="<?php echo get_term_link( $value->term_id,'azp_category' ) ?>"><?php echo $value->name ?></a></span> -->
					</h4>

					<?php  if($TaxProducts){
						echo '<ol class="az_cat_sub">';
						foreach ($TaxProducts as $s_key => $tp){
							$current = '';
							if ( $tp->ID == $wer && get_class($CurrentQuery) == 'WP_Post') 
								$current = 'current';
							$pt = apply_filters('the_title', $tp->post_title); //add 22-04-2018

							echo '<li class="'.$current.'"><a href="'.get_permalink($tp->ID).'">'.$pt.'</a></li>';
						}
						echo '</ol>';
					}
					
				?></li><?php 
			} /*  subcat foreach */




				?>
			</ul>
		</div>
		<?php 

		} /* if subcat is NOT empty */

	}
	echo '</div>';
?>
