<?php
/*
* Register the new widget classes here so that they show up in the widget list
*/
function load_widgets() {
	register_widget('LatestTweets');
	// register_widget('ThemeWidgetExample');
	register_widget('ThemeWidgetTags');
}
add_action('widgets_init', 'load_widgets');

/*
* Displays a block with latest tweets from particular user
*/
class LatestTweets extends ThemeWidgetBase {
	function LatestTweets() {
		$widget_opts = array(
			'classname' => 'theme-widget-tweets',
			'description' => 'Displays a block with your latest tweets'
		);
		$this->WP_Widget('theme-widget-latest-tweets', 'Latest Tweets', $widget_opts);
		$this->custom_fields = array(
			array(
				'name'=>'title',
				'type'=>'text',
				'title'=>'Title',
				'default'=>''
			),
			array(
				'name'=>'username',
				'type'=>'text',
				'title'=>'Username',
				'default'=>''
			),
			array(
				'name'=>'count',
				'type'=>'text',
				'title'=>'Number of Tweets to show',
				'default'=>'5'
			),
		);
	}
	
	/*
	* Called when rendering the widget in the front-end
	*/
	function front_end($args, $instance) {
		extract($args);
		$tweets = TwitterHelper::get_tweets($instance['username'], $instance['count']);
		if (!empty($tweets)) {
			if ($instance['title'])
				echo $before_title . $instance['title'] . $after_title;
		}
		?>
			<?php foreach ($tweets as $tweet): ?>
				<p><?php echo $tweet->tweet_text ?> - <span class="time"><?php echo $tweet->time_distance ?> ago</span></p>
			<?php endforeach ?>
		<?php
	}
}

/*
* An example widget
*/
class ThemeWidgetExample extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
	function ThemeWidgetExample() {
		$widget_opts = array(
			'classname' => 'theme-widget', // class of the <li> holder
			'description' => __( 'Displays a block with title/text' ) // description shown in the widget list
		);
		// Additional control options. Width specifies to what width should the widget expand when opened
		$control_ops = array(
			//'width' => 350,
		);
		// widget id, widget display title, widget options
		$this->WP_Widget('theme-widget-example', 'Theme Widget - Example', $widget_opts, $control_ops);
		$this->custom_fields = array(
			array(
				'name'=>'title', // field name
				'type'=>'text', // field type (text, textarea, integer etc.)
				'title'=>'Title', // title displayed in the widget form
				'default'=>'Hello World!' // default value
			),
			array(
				'name'=>'text',
				'type'=>'textarea',
				'title'=>'Content', 
				'default'=>'Lorem Ipsum dolor sit amet'
			),
		);
	}
	
	/*
	* Called when rendering the widget in the front-end
	*/
	function front_end($args, $instance) {
		extract($args);
		if ($instance['title'] != '') {
			echo $before_title . $instance['title'] . $after_title;
		}
		?>
		<p><?php echo $instance['text'];?></p>
		<?php
	}
}

class ThemeWidgetTags extends ThemeWidgetBase {
	/*
	* Register widget function. Must have the same name as the class
	*/
	function ThemeWidgetTags() {
		$widget_opts = array(
			'classname' => 'theme-tags', // class of the <li> holder
			'description' => __( 'Displays a tags with count' ) // description shown in the widget list
		);
		// Additional control options. Width specifies to what width should the widget expand when opened
		$control_ops = array(
			//'width' => 350,
		);
		// widget id, widget display title, widget options
		$this->WP_Widget('theme-widget-tags', 'Theme Widget - Tags', $widget_opts, $control_ops);
		$this->custom_fields = array(
			array(
				'name'=>'title', // field name
				'type'=>'text', // field type (text, textarea, integer etc.)
				'title'=>'Title', // title displayed in the widget form
				'default'=>'Tags' // default value
			),
		);
	}
	
	/*
	* Called when rendering the widget in the front-end
	*/
	function front_end($args, $instance) {
		extract($args);
		if ($instance['title'] != '') {
			echo $before_title . $instance['title'] . $after_title;
		}
		$tags = get_tags();
		if($tags) :
			?>
			<ul>
				<?php
				foreach($tags as $tag) :
					?>
					<li><a href="<?php echo get_tag_link($tag->term_id); ?>"><?php echo $tag->name; ?>(<?php echo $tag->count; ?>)</a></li>
					<?php
				endforeach;
				?>
			</ul>
			<?php
		endif;
		?>
		
		<?php
	}
}
?>