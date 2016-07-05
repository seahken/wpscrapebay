<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php esc_attr_e( 'WP Scrape eBay', 'wp_admin_style' ); ?></h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<?php if(!isset($wpscrapebay_url) || $wpscrapebay_url == ''): ?>
						<div class="postbox">

							<h2><span>Settings:</span></h2>

							<div class="inside">
								<form name="wpscrapebay_url_form" method="post" action="">

								<input type="hidden" name="wpscrapebay_form_submitted" value="Y">

								<table class="form-table">
									<tr>
										<td>
											<label for="wpscrapebay_url">URL:</label>
										</td>
										<td>
											<input name="wpscrapebay_url" id="wpscrapebay_url" type="text" value="" class="regular-text" />
										</td>
									</tr>								
								</table>

								<p>
									<input class="button-primary" type="submit" name="wpscrapebay_form_submit" value="Save" /> 
								</p>

								</form>


							</div>
							<!-- .inside -->

						</div>
						<!-- .postbox -->
					<?php else: ?>
						<div class="postbox">

							<h2><span>Settings:</span></h2>

							<div class="inside">
								<form name="wpscrapebay_url_form" method="post" action="">

								<input type="hidden" name="wpscrapebay_form_submitted" value="Y">

								<table class="form-table">
									
									<tr>
										<td>
											<label for="wpscrapebay_url">URL:</label>
										</td>
										<td>
											<input name="wpscrapebay_url" id="wpscrapebay_url" type="text" value="<?php echo $wpscrapebay_url; ?>" class="regular-text" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="wpscrapebay_tag_name">Main Page Tag Name:</label>
										</td>
										<td>
											<input name="wpscrapebay_tag_name" id="wpscrapebay_tag_name" type="text" value="<?php echo $wpscrapebay_tag_name; ?>" class="regular-text" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="wpscrapebay_attribute">Main Page Attribute:</label>
										</td>
										<td>
											<input name="wpscrapebay_attribute" id="wpscrapebay_attribute" type="text" value="<?php echo $wpscrapebay_attribute; ?>" class="regular-text" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="wpscrapebay_class_name">Main Page Attribute Name:</label>
										</td>
										<td>
											<input name="wpscrapebay_class_name" id="wpscrapebay_class_name" type="text" value="<?php echo $wpscrapebay_class_name; ?>" class="regular-text" />
										</td>
									</tr>									
								</table>

								<p>
									<input class="button-primary" type="submit" name="wpscrapebay_form_submit" value="Save" /> 
								</p>

								</form>
							</div>
							<!-- .inside -->

						</div>
						<!-- .postbox -->

					<div class="postbox" style="width:675px; display:inline-block">

						<h2><span>Scrapped Products</span></h2>

						<div class="inside">

							<form name="post_submit_form" method="post" action="">
								<p>
									<input type="hidden" name="add_post_submitted" value="Y">
									<input class="button-primary" type="submit" name="add_post" value="Add All Products" />

								</p>
							</form>

							<?php for ($i=0; $i<count($database); $i++) : ?>
							<p><?php echo $i+1 . '. <a href="' . $database[$i]["link"] . '">' .  $database[$i]["name"] .'</a>'; ?></p>
							<form name="post_add_product" method="post" action="">
								<p>
									<input type="hidden" name="add_product_submitted" value="<?php echo $i ?>">
									<input class="button-primary" type="submit" name="add_product" value="Add Product" />

								</p>
							</form>
							<?php endfor; ?>

							<form name="post_submit_form" method="post" action="">
								<p>
									<input type="hidden" name="add_post_submitted" value="Y">
									<input class="button-primary" type="submit" name="add_post" value="Add All Products" />

								</p>
							</form>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->
					<div class="postbox" style="width:675px; display:inline-block; float:right">

						<h2><span>Added Items</span></h2>

						<div class="inside">

							<form name="post_add_update_form" method="post" action="">
								<p>
									<input type="hidden" name="add_update_submitted" value="Y">
									<input class="button-primary" type="submit" name="add_update_products" value="Add/Update Products" />
								</p>
							</form>
							<?php for ($i=0; $i<count($database_added); $i++) : ?>
							<p><?php echo $i+1 . '. <a href="' . $database_added[$i]["link"] . '">' .  $database_added[$i]["name"] .'</a>'; ?></p>
							<form name="post_remove_product" method="post" action="">
								<p>
									<input type="hidden" name="remove_post_submitted" value="<?php echo $i ?>">
									<input class="button-primary" type="submit" name="remove_product" value="Remove Product" />

								</p>
							</form>
							<?php endfor; ?>

							<form name="post_add_update_form" method="post" action="">
								<p>
									<input type="hidden" name="add_update_submitted" value="Y">
									<input class="button-primary" type="submit" name="add_update_products" value="Add/Update Products" />
								</p>
							</form>
							<form name="remove_form" method="post" action="">
								<p>
									<input type="hidden" name="remove_all_submitted" value="Y">
									<input class="button-primary" type="submit" name="remove_all_products" value="Remove all Products" />
								</p>
							</form>
							<form name="publich_form" method="post" action="">
								<p>
									<input type="hidden" name="publish_all_submitted" value="Y">
									<input class="button-primary" type="submit" name="publish_all_products" value="Publish all Products" />
								</p>
							</form>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->


					<?php endif; ?>

					<?php if ($wpscrapebay_display_html == true) : ?>

					<div class="postbox">

						<h2><span><?php esc_attr_e( 'HTML Feed', 'wp_admin_style' ); ?></span></h2>

						<div class="inside">
							<p><?php echo $wpscrapebay_html; ?></p>
							<pre><code>
								<?php var_dump( $wpscrapebay_html); ?>
							</pre></code>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

					<?php endif; ?>

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>


			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h2><span><?php esc_attr_e(
									'Sidebar Content Header', 'wp_admin_style'
								); ?></span></h2>

						<div class="inside">
							<p><?php esc_attr_e(
									'Everything you see here, from the documentation to the code itself, was created by and for the community. WordPress is an Open Source project, which means there are hundreds of people all over the world working on it. (More than most commercial platforms.) It also means you are free to use it for anything from your cat’s home page to a Fortune 500 web site without paying anyone a license fee and a number of other important freedoms.',
									'wp_admin_style'
								); ?></p>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->
