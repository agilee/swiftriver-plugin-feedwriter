<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * River Feedwriter
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @subpackage Controllers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL) 
 */
class Controller_River_Feedwriter extends Controller_River {
	
	public function action_index()
	{
		include_once Kohana::find_file('vendor', 'feedwriter/FeedWriter');
		include_once Kohana::find_file('vendor', 'feedwriter/FeedItem');
		
		// The maximum droplet id for pagination and polling
		$max_droplet_id = Model_River::get_max_droplet_id($this->river->id);

		//Get Droplets
		$droplets_array = Model_River::get_droplets($this->user->id, 
							$this->river->id, 0, 1, $max_droplet_id, NULL, 
							array(), $this->photos);
		
		$format = $this->request->param('format');
		
		$feed = new FeedWriter($format == 'rss' ? RSS2 : ATOM);
		$feed->setTitle($this->page_title);
		$feed->setLink(URL::site($this->river->get_base_url(), TRUE));
		$feed->setDescription('Drops from the '.$this->river->river_name
				                      .' river.');
		foreach ($droplets_array['droplets'] as $drop)
		{
			$newItem = $feed->createNewItem();
			$newItem->setTitle($drop['droplet_title']);
			$newItem->setLink($drop['original_url']);
			$newItem->setDate($drop['droplet_date_pub']);
			$newItem->setDescription($drop['droplet_content']);
			$feed->addItem($newItem);
		}
		$feed->genarateFeed();
		exit;
	}
}