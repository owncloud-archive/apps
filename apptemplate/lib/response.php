<?php

/**
* ownCloud - App Template Example
*
* @author Bernhard Posselt
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/


namespace OCA\AppTemplate;

interface Response {
        function render();
}

/**
 * Response for a normal template
 */
class TemplateResponse implements Response {

        private $templateName;
        private $params;
        private $appName;
        private $renderAs;

        /**
         * @param string $appName: the name of your app
         * @param string $templateName: the name of the template
         */
        public function __construct($appName, $templateName) {
                $this->templateName = $templateName;
                $this->appName = $appName;
                $this->params = array();
                $this->renderAs = 'user';
        }


        /**
         * @brief sets template parameters
         * @param array $params: an array with key => value structure which sets template
         *                       variables
         */
        public function setParams($params){
                $this->params = $params;
        }


        /**
         * @brief sets the template page
         * @param string $renderAs: admin, user or blank: admin renders the page on
         *                          the admin settings page, user renders a normal
         *                          owncloud page, blank renders the template alone
         */
        public function renderAs($renderAs='user'){
                $this->renderAs = $renderAs;
        }


        /**
         * Returns the rendered html
         * @return the rendered html
         */
        public function render(){
                if($this->renderAs === 'blank'){
                        $template = new \OCP\Template($this->appName, $this->templateName);
                } else {
                        $template = new \OCP\Template($this->appName, $this->templateName,
                                                                                        $this->renderAs);
                }

                foreach($this->params as $key => $value){
                        $template->assign($key, $value, false);
                }

                return $template->fetchPage();
        }

}


/**
 * A renderer for JSON calls
 */
class JSONResponse implements Response {

        private $name;
        private $data;
        private $appName;

        /**
         * @param string $appName: the name of your app
         */
        public function __construct($appName) {
                $this->appName = $appName;
                $this->data = array();
                $this->error = false;
        }

        /**
         * @brief sets values in the data json array
         * @param array $params: an array with key => value structure which will be
         *                       transformed to JSON
         */
        public function setParams($params){
                $this->data['data'] = $params;
        }


        /**
         * @brief in case we want to render an error message, also logs into the
         *        owncloud log
         * @param string $message: the error message
         * @param string $file: the file where the error occured, use __FILE__ in
         *                      the file where you call it
         */
        public function setErrorMessage($msg, $file){
                $this->error = true;
                $this->data['msg'] = $msg;
                \OCP\Util::writeLog($this->appName, $file . ': ' . $msg, \OCP\Util::ERROR);
        }


        /**
         * Returns the rendered json
         * @return the rendered json
         */
        public function render(){

                ob_start();

                if($this->error){
                \OCP\JSON::error($this->data);
                } else {
                \OCP\JSON::success($this->data);
                }

                $result = ob_get_contents();
                ob_end_clean();

                return $result;
        }

}