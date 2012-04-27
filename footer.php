			</div>
			<!-- End Shell -->
		</div>
		<!-- End Main -->
		<!-- Footer -->
		<script type="text/javascript">Cufon.now();</script>
		<div id="footer">
			<!-- Shell -->
			<div class="shell">
				<a href="#top" class="top-link">Back to top?</a>
				<!-- F Cols -->
				<div class="f-cols">
					<ul>
						<?php dynamic_sidebar('footer-sidebar'); ?>
					</ul>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End F Cols -->
				<?php social_links(); ?>
				<!-- F Bottom -->
				<div class="f-bottom">
					<p class="copy"><?php echo get_option('copyright'); ?></p>
					<div class="links">
						<?php $args = array(	
							'container' => false,
							'theme_location' => 'footer-menu',
							'fallback_cb' => 'wp_page_menu',
							);
						wp_nav_menu( $args ); ?> 

					</div>
					<div class="cl">&nbsp;</div>
				</div>
				<!-- End F Bottom -->
			</div>
			<!-- End Shell -->
		</div>
		<!-- End Footer -->
	</div>
	<!-- End Wrapper -->
		<?php wp_footer(); ?>
	</body>
</html>