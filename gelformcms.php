<?php 
// in case the session is not started, we need it for storing "signed in" - requires  PHP >= 5.4.0
// for versions < 5.4 use session_id() == ''
if (session_status() == PHP_SESSION_NONE) 
{
	session_start();
}



/**
 * Gelform CMS
 * Created by Corey Maass at Gelform Inc
 * http://gelform.com/gelformcms
 *
 *
 * 
 * Description
 * ============================
 * Why does the world need another CMS? When working with most MVC 
 * frameworks, it's a huge pain to wedge another cms into my existing 
 * views. With this class, you define 3 constants, add one folder and 
 * you're done. Create includes, and put them in your views wherever
 * you like.
 *
 *
 * 
 * How to set it up
 * ============================
 * 1. Create a route in your application that can accept GET and POST
 * requests. 
 *
 * For example, in the Slim Framework, I added this route:
 * $app->map('/cms/', array(new Controller_Cms(), 'index'))
 * ->via('GET', 'POST');
 *
 * Or in Zend, add a controller called CmsController and add an 
 * action called indexAction();
 *
 * 2. Add an assets folder accessible from the web and make it writeable. So 
 * if your webroot is /public, you might add a folder called /cms (so your
 * path would look like /public/cms). Then "chmod 777 cms" so it's writeable. 
 *
 * 3. Back in your controller action, define 3 constants:
 * // set this to be any string, or pull it from a config
 * define('GELFORMCMS_PASSWORD', 'password'); 
 *
 * // set this to the path of the assets folder you created in step 2. 
 * define('GELFORMCMS_PATH', APPLICATION_DIR . 'public_html/cms');
 *
 * // Redundant, I know, but set this to the absolute path to the 
 * // same assets folder.
 * define('GELFORMCMS_URI', '/cms');
 *
 * 4. Include the GelformCMS class:
 * require APPLICATION_DIR . 'model/gelformcms.php';
 *
 * 5. That's it! Visit the route you created, and you shuld be asked
 * to sign in.
 *
 *
 * 
 * How to use it
 * ============================
 * 1. Sign in using the password you set in the constant 
 * GELFORMCMS_PASSWORD
 * 2. A "section" is just an html blob. Click the button to "create
 * a new section"
 * 3. Give it a name, and add HTML to your hearts content.
 * 4. Use the "images" button to upload images, or select images 
 * you've uploaded, previously. They will be uploaded to a "img"
 * folder in the assets folder your created. 
 * 5. Save it. 
 * 6. Now at the bottom, below the HTML form, you'll see a link for
 * the "PHP include statement". Copy this, and put it in your view
 * scripts wherever you want the HTML to render.
 *
 *
 *
 * technical stuff
 * ============================
 * When you run it the first time, it will add 3 new folders in 
 * the folder you created. If you get an error, make sure the folder
 * is writeable.
 *
 * The CMS uses jquery, TinyMCE and Twitter Bootstrap, loaded from
 * CDNs. So you'll need an internet connection for presentation,
 * image upload and some behavior. The core of the app should work
 * without it, however. And it looks pretty good on mobile!
 * 
 *
 * 
 * To do
 * ============================
 * - option to import CSS into TinyMCE
 * - limit number of revisions (delete after a certain amount)
 * - multiple users, user management
 * - put sections in buckets, collections
 * 
 */

class GelformCMS
{
	// where we store the html templates
	protected $html = array();

	// where we pass data to rendered html
	protected $viewData = array();

	// the folders where we put our data
	protected $folders = array (
		'data' => 'data',
		'html' => 'html',
		'img' => 'img'
	);

	// the orgainzation for each data item
	protected $schemas = array (
		'section' => array (
			'id' => '',
			'name' => '',
			'updated' => '',
			'isactive' => '',
			'revisions' => array ()
		),
		'revision' => array (
			'date' => '',
			'html' => ''
		)
	);



	// called first from construct to create our html templates
	private function _htmlSetup()
	{
		$this->html['wrapper'] = function()
		{
			extract($this->viewData);
		?>
			<!DOCTYPE html>
			<html>
				<head>
					<title>CMS</title>
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
					<!-- <link href="//netdna.bootstrapcdn.com/bootswatch/3.0.0/flatly/bootstrap.min.css" rel="stylesheet"> -->

					<style>
					body {margin: 6em 0;}
					.navbar-btn {margin-left: .618em;}
					body .modal { width: 90%; margin: 0 auto; }
					body .modal-dialog {xxheight: 90%; width: auto;}
					iframe {height: 1px; width: 1px;}
					.thumbnail {max-height: 10em; overflow: hidden;}
					</style>
				</head>
				<body>

					<div class="navbar navbar-default navbar-fixed-top">
						<div class="container">
							<div class="navbar-header">
								<a href="<?= $_SERVER['REQUEST_URI'] ?>" class="navbar-brand">Gelform CMS</a>
								<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
								</button>
							</div><!-- navbar-header -->
<?php if ( $signedIn ) : ?>
							<div class="navbar-collapse collapse" id="navbar-main">
								<ul class="nav navbar-nav navbar-right">
									<li>
										<form action="" method="post">
											<p>
												<button type="submit" class="btn navbar-btn">Sign out</button>
											</p>
											<input type="hidden" name="action" value="signout" />
										</form>
									</li>
									<li>
										<form action="" method="post">
											<p>
												<button type="submit" class="btn navbar-btn">New section</button>
											</p>
											<input type="hidden" name="action" value="new" />
										</form>
									</li>
									<li>
										<form action="" method="get">
											<p>
												<button type="submit" class="btn navbar-btn">Edit another</button>
											</p>
										</form>
									</li>
								</ul>
							</div><!-- navbar -->
<?php endif // signedIn ?>
						</div><!-- container -->
					</div><!-- navbar -->

					<div class="container" id="container-body">
<?php if ( $alert ) : ?>
						<p class="alert alert-success">
							<?= $alert ?>
						</p><!-- alert -->
<?php endif ?>
						<?= $body ?>
					</div><!-- container -->

					<script src="http://code.jquery.com/jquery.js"></script>
					<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
					<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
					<script>
						// http://www.tinymce.com/wiki.php/Plugins
						tinymce.init({
							selector:'textarea',
							plugins: "link, preview, image, code, autolink, autoresize"
						});

						function populateGallery()
						{
							var imagesArr = $("#iframe-images")[0].contentWindow.imagesArr;

							var $modalimagesrow = $('#modal-gallery-row').empty();

							if ( undefined !== imagesArr )
							{
								for (var i = imagesArr.length - 1; i >= 0; i--) 
								{
									var src = imagesArr[i];

									$('<div class="col-md-3">'
										+ '<a href="#" class="thumbnail">'
										+ '<img src="' + src + '"></a>'
										+ '</div>'
									)
									.appendTo($modalimagesrow);
								};
							};
						}

						$(document).ready(function(){
							$('#modal-gallery')
							.on('show.bs.modal', function () 
							{
								populateGallery();
							});

							$("#iframe-images").on('load', function(){
								populateGallery();
							});

							$('#modal-gallery-row').on(
								'click',
								'img',
								function()
								{
									var src = $(this).prop('src');

									// http://stackoverflow.com/a/5193042/38241
									var ed = tinyMCE.get('html');
									var range = ed.selection.getRng();
									var newNode = ed.getDoc().createElement ( "img" );
									newNode.src= src;
									range.insertNode(newNode); 

									$('#modal-gallery').modal('hide');

									return false;
								}
							);

							$("#include").on("click", function () {
								$(this).select();
							});
						});
					</script>
				</body>
			</html>
		<?php }; // wrapper



		$this->html['signin'] = function()
		{
			extract($this->viewData);
		?>
			<div class="col-md-4 col-md-offset-4">
				<h1>Sign in</h1>
				<div class="well">
					<form action="" method="post" role="form">
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" name="password" id="password" class="form-control" placeholder="Password">
						</div>
						<p>
							<button type="submit" class="btn btn-block">Sign in</button>
							<input type="hidden" name="action" value="signin" />
						</p>
					</form>
				</div><!-- well -->
			</div><!-- col -->

		<?php }; // signin



		$this->html['chooser'] = function()
		{
			extract($this->viewData);
		?>
<?php if ( $sections ) : ?>
			<div class="col-md-4 col-md-offset-4">
				<h1>What do you want to edit?</h1>
				<div class="well">
					<form action="" method="post" role="form">
						<div class="form-group">
							<label for="id">Sections</label>

							<select name="id" id="id" class="form-control">
								<option value="">-- Choose one --</option>
<?php foreach ($sections as $id => $name) : ?>
								<option value="<?= $id ?>"><?= $name ?></option>
<?php endforeach ?>
							</select>
						</div>
						<p>
							<button type="submit" class="btn btn-block">Edit it!</button>
							<input type="hidden" name="action" value="edit" />
						</p>
					</form>

					<p class="lead text-center">Or </p>
<?php endif // sections ?>
					<form action="" method="post" role="form">
						<button type="submit" class="btn btn-block">Create a new section</button>
						<input type="hidden" name="action" value="new" />
					</form>
				</div><!-- well -->
			</div><!-- col -->
		<?php }; // chooser



		$this->html['edit'] = function()
		{
			extract($this->viewData);
		?>
				<h1>Make your changes</h1>
				<div class="well">
					<form action="" method="post" role="form">
						<div class="form-group">
							<label for="name">Name:</label>
							<input type="text" name="name" id="name" class="form-control" placeholder="i.e. about us page" value="<?= $section->name ?>">
						</div>

						<p class="pull-right">
							<a data-toggle="modal" href="#modal-gallery" class="btn btn-primary">Images</a>
						</p>

						<div class="form-group" style="padding-top: 1.618em;">
							<label for="html">Content:</label>
							<?php $newest = max(array_keys($section->revisions)); ?>
							<textarea name="html" id="html" rows="10" class="form-control"><?= $section->revisions[$newest]->html ?></textarea>
						</div>
						<p>
							<button type="submit" class="btn btn-block">Save it!</button>
							<input type="hidden" name="action" value="save" />
							<input type="hidden" name="id" value="<?= $section->id ?>" />
						</p>
					</form>
				</div><!-- well -->

				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#accordion-include">PHP include statement:</a>
							</h4>
						</div>
						<div id="accordion-include" class="panel-collapse collapse">
							<div class="panel-body">
								<form class="form-horizontal" role="form">
									<div class="form-group">
										<div class="col-lg-12">
											<input type="text" class="form-control" id="include" value="&lt;?php include &quot;<?= GELFORMCMS_PATH . '/' . $this->folders['html'] . '/' ?><?= $section->id ?>.html&quot;; ?&gt;">
										</div>
									</div>
								</form>
							</div>
						</div>
					</div><!-- panel -->
				</div><!-- panel-group -->

				<div class="modal fade" id="modal-gallery">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<form action="?action=upload" method="post" target="iframe-images" enctype="multipart/form-data" class="form-inline well text-center" role="form">
									<div class="form-group">
										<label class="sr-only" for="image">Image:</label>
										<input type="file" name="image" id="image" />  
									</div>
									<button type="submit" class="btn btn-sm">upload</button>
								</form>
							</div>
							<div class="modal-body">
								<div class="row" id="modal-gallery-row">
									
								</div><!-- row -->
							</div><!-- modal-body -->
							<iframe name="iframe-images" id="iframe-images" src="<?= substr( $_SERVER['REQUEST_URI'], 0, strrpos( $_SERVER['REQUEST_URI'], "?")) ?>?action=upload"></iframe>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->
		<?php }; // chooser



		$this->html['gallery'] = function()
		{
			extract($this->viewData);
		?>
			<script>
			var imagesArr = <?= json_encode($images) ?>;
			</script>
		<?php }; // chooser
	}



	protected function render($template, $useWrapper = TRUE)
	{
		if ( !$useWrapper )
		{
			call_user_func($this->html[$template]);
			exit;
		}

		ob_start();
		call_user_func($this->html[$template]);
		$body = ob_get_clean();

		$this->viewData['body'] = $body;

		if ( isset($_SESSION['alert']) )
		{
			$this->viewData['alert'] = $_SESSION['alert'];
			unset($_SESSION['alert']);
		}

		call_user_func($this->html['wrapper']);
		exit;
	}



	public function __construct()
	{
		// setup html templates
		$this->_htmlSetup();



		// make sure our folders exist
		foreach ($this->folders as $folder) 
		{
			if ( !is_dir(GELFORMCMS_PATH . '/' . $folder) )
			{
				if ( !mkdir(GELFORMCMS_PATH . '/' . $folder) ) exit('cms asset folder is not writable');
			}
		}



		// if not post and not signed in
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' && ! (bool) $_SESSION['GELFORMCMS_admin_signedIn'] )
		{
			$this->render('signin');
		}



		if ( isset($_SESSION['GELFORMCMS_admin_signedIn']) )
		{
			$this->viewData['signedIn'] = TRUE;
		}



		// handle file uploads
		if ( $_GET['action'] == 'upload' )
		{
			if( !empty($_FILES) && $_FILES["image"]["error"] == 0 ) 
			{
				// save it
				move_uploaded_file(
					$_FILES["image"]["tmp_name"],
					GELFORMCMS_PATH . '/' . $this->folders['img'] . '/' . $_FILES["image"]["name"]
				);
			}

			$dir = new DirectoryIterator(GELFORMCMS_PATH . '/' . $this->folders['img']);
			$imgArr = array();
			foreach ($dir as $fileinfo) 
			{
				if (!$fileinfo->isDot()) 
				{
					$name = $fileinfo->getFilename();
					$imgArr[] = GELFORMCMS_URI . '/' . $this->folders['img'] . '/' . $name;
				}
			}

			$this->viewData['images'] = $imgArr;
			$this->render('gallery', FALSE);
			exit;

		} // upload



		// if not post, show chooser
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) 
		{
			$dir = new DirectoryIterator(GELFORMCMS_PATH . '/' . $this->folders['data']);
			$sectionsArr = array();
			foreach ($dir as $fileinfo) 
			{
				if (!$fileinfo->isDot()) 
				{
					$id = $fileinfo->getFilename();

					$section = unserialize(file_get_contents(GELFORMCMS_PATH . '/' . $this->folders['data'] . '/' . $id));

					$sectionsArr[$id] = $section->name;
				}
			}

			$this->viewData['sections'] = $sectionsArr;
			$this->render('chooser');
		} // chooser



		// if is post
		switch ($_POST['action']) 
		{
			// process signing in
			case 'signin':
				if ( $_POST['password'] != GELFORMCMS_PASSWORD )
				{
					$this->viewData['alert'] = 'Whoops! That password was incorrect. Give it another try?';

					$this->render('signin');
				}



				// if password is correct, set the session variable
				$_SESSION['GELFORMCMS_admin_signedIn'] = TRUE;

				// and reload the page
				$_SESSION['alert'] = 'Welcome back!';
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
				break;



			case 'signout':
				unset($_SESSION['GELFORMCMS_admin_signedIn']);

				// and reload the page
				$_SESSION['alert'] = 'Okay, you\'ve been signed out.';
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
				break;



			case 'new':
				// create a uniq object id
				$id = uniqid();

				// create our section object
				$section = (object) $this->schemas->section;
				$section->id = $id;



			// continue from 'new'
			case 'edit':
				// load our section object if we're editing it
				if ( !isset($id) )
				{
					$id = $_POST['id'];

					// try loading the saved section object
					if ( !is_file(GELFORMCMS_PATH . '/' . $this->folders['data'] . '/' . $id) ) 
					{
						// if it's not found, alert the user
						$_SESSION['alert'] = 'Whoops, there was a problem loading that section.';
						header('Location: '.$_SERVER['REQUEST_URI']);
						exit;
					}

					// otherwise, load it
					$section = unserialize(file_get_contents(GELFORMCMS_PATH . '/' . $this->folders['data'] . '/' . $id));
				}

				// populate the edit template
				$this->viewData['section'] = $section;
				$this->render('edit');

				break;



			case 'save':
				// get id
				$id = $_POST['id'];

				// try to load existing
				if ( is_file(GELFORMCMS_PATH . '/' . $this->folders['data'] . '/' . $id) ) 
				{ 
					$section = unserialize(file_get_contents(GELFORMCMS_PATH . '/' . $this->folders['data'] . '/' . $id));
				}
				else
				{
					// otherwise, create a new section object
					$section = (object) $this->schemas->section;
					$section->id = $_POST['id'];
				}

				

				$now = date('U');

				// overwrite the name, in case they changed it
				$section->name = $_POST['name'];

				// add a new revision
				$revision = (object) $this->schemas->revision;
				$revision->html = $_POST['html'];
				$revision->date = $now;

				$section->updated = $now;
				$section->revisions[$now] = $revision;

				// save the data object
				file_put_contents(GELFORMCMS_PATH . '/' . $this->folders['data'] . '/' . $_POST['id'], serialize($section));

				// save the html for quick rendering
				file_put_contents(GELFORMCMS_PATH . '/' . $this->folders['html'] . '/' . $_POST['id'] . '.html', $section->html);

				$this->viewData['alert'] = 'Saved!';

				$this->viewData['section'] = $section;
				$this->render('edit');

				break;
		}



	} // init



} // class



new GelformCMS();

exit;


