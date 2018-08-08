<?php

namespace Grav\Plugin\Publish;

use Grav\Common\Grav;
use Grav\Common\Helpers\Base32;
use Grav\Common\Utils;
use Grav\Plugin\Publish\Controller;

/**
 * Class Controller
 * @package Grav\Plugin\Directory
 */
class AdminController extends Controller
{


  public function taskRun()
  {
    if (!$this->authorizeTask('run publish', ['admin.publish', 'admin.super'])) {
        return false;
    }
    $locator = Grav::instance()['locator'];
    $config = Grav::instance()['config']->get('plugins.publish');
    $user_path = $locator->findResource('user://');

    if (!empty($config['touch']) && ($config['touch'] != '')) {      
      if ($config['touch'][0] != '/') {
        $config['touch'] = $user_path.DIRECTORY_SEPARATOR.$config['touch'];
      }      
      try {
          touch($config['touch']);
          $this->admin->json_response = [
            'status'  => 'success'
          ];
      } catch (\Exception $e) {
        $this->admin->json_response = [
          'status'  => 'error',
          'message' => $e->getMessage()
        ];
        return false;
      }
    }
    
    if (!empty($config['webhook']) && ($config['webhook'] != '')) {
      $client = new \GuzzleHttp\Client();
      try {
        $response = $client->request('POST', $config['webhook'], []);
        $this->admin->json_response = [
          'status'  => $response->getStatusCode() === 200 ? 'success' : 'error',
          'message' => $responce->getBody()
        ];
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $this->admin->json_response = [
            'status'  => 'error',
            'message' => $e->getMessage()
          ];
          return false;
      }
    }
    return true;
  }


}
