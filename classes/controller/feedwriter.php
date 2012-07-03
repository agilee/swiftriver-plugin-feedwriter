<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Controller for the FeedWriter plugin
 *
 * @package   SwiftRiver
 * @author    Ushahidi Team
 * @category  Plugins
 * @copyright (c) 2008-2012 Ushahidi Inc <http://ushahidi.com>
 */

include 'plugins/feedwriter/classes/libs/rss.php';
include 'plugins/feedwriter/classes/libs/atom.php';

class Controller_Feedwriter extends Controller_Swiftriver {
	protected $bucket;
	protected $droplets;
	protected $ready;

	public function before()
	{
		parent::before();
		$this->auto_render = FALSE;
		$this->bucket = ORM::factory('bucket')
		     ->where('bucket_name_url', '=', $this->request->param('name'))
		     ->find();

		if ($this->bucket->loaded())
		{
			// Bucket isn't published and logged in user isn't owner
			if ( ! $this->bucket->bucket_publish AND
			    ! $this->bucket->is_owner($this->user->id) AND
			   ( ! $this->bucket->public_token OR
			   Arr::get($_GET, 't', '') != $this->bucket->public_token))
			{
				throw new HTTP_Exception_404();
			}

			$droplets = Model_Bucket::get_droplets($this->user->id,
			            $this->bucket->id, 0, NULL, PHP_INT_MAX, FALSE, array(),
			            10);
			$this->droplets = $droplets['droplets'];
		}
		else
		{
			throw new HTTP_Exception_404();
		}
	}

	public function action_rss()
	{
		$account = $this->bucket->account->account_path;
		$name = $this->bucket->bucket_name;

		$feed = new Feedwriter_Rss;
		$feed->set_title($account.' / '.$name);
		$feed->set_link(URL::site($this->bucket->get_base_url(), TRUE));
		$feed->set_description('Drops from '.$this->bucket->bucket_name
		                      .' on Swiftriver');
		$feed->set_language(Kohana::$config->load('feedwriter')->get('language'));
		$feed->set_copyright(Kohana::$config->load('feedwriter')->get('copyright'));

		if (count($this->droplets) > 0)
		{
			$feed->set_updated($this->droplets[0]['droplet_date_pub']);
			foreach ($this->droplets as $k => $v)
			{
				$url = URL::site($this->request->param('account').'/bucket/'
				                .$this->request->param('name').'/drop/'
				                .$v['id'], TRUE);
				$feed->add_item(array(
					'title'       => $v['droplet_title'],
					'guid'        => $url,
					'link'        => $url,
					'description' => $v['droplet_content'],
					'time'        => $v['droplet_date_pub']
				));
			}
		}

		$gen = $feed->generate();
		$this->response->headers('Content-Type', 'text/xml');
		$this->response->headers('ETag', md5($gen));
		echo $gen;
	}

	public function action_atom()
	{
		$account = $this->bucket->account->account_path;
		$name = $this->bucket->bucket_name;

		$feed = new Feedwriter_Atom;
		$feed->set_title($account.' / '.$name);
		$feed->set_link(URL::site($this->bucket->get_base_url(), TRUE));
		$feed->set_author('SwiftRiver / '.$account, URL::site($account, TRUE));
		$feed->set_description('Drops from '.$this->bucket->bucket_name.
		                      .' on Swiftriver');
		$feed->set_copyright(Kohana::$config->load('feedwriter')->get('copyright'));

		if (count($this->droplets) > 0)
		{
			$feed->set_updated($this->droplets[0]['droplet_date_pub']);
			foreach ($this->droplets as $k => $v)
			{
				$url = URL::site($this->request->param('account').'/bucket/'
				                .$this->request->param('name').'/drop/'
				                .$v['id'], TRUE);
				$feed->add_item(array(
					'title'   => $v['droplet_title'],
					'id'      => $url,
					'link'    => $url,
					'content' => $v['droplet_content'],
					'time'    => $v['droplet_date_pub'],
					'author'  => $v['identity_name'],
				));
			}
		}

		$gen = $feed->generate();
		$this->response->headers('Content-Type', 'text/xml');
		$this->response->headers('ETag', md5($gen));
		echo $gen;
	}
}

?>
