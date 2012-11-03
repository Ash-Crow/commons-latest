<div>
	<?php 
		$options = $this->getAdminOptions();
                if(!empty($args['category'])) {
                	$category=$args['category'];
                } else {
                        $category=$options['category_name'];
                }


		echo $this->getLastPictures($category,$options['widget_width'],1,0);
	?>
</div>
