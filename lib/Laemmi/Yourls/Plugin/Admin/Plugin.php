<?php
/**
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @category    laemmi-yourls-admin
 * @package     Plugin.php
 * @author      Michael Lämmlein <ml@spacerabbit.de>
 * @copyright   ©2015 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       04.11.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\Plugin\Admin;

use Laemmi\Yourls\Plugin\AbstractDefault;

/**
 * Class Plugin
 *
 * @package Laemmi\Yourls\Plugin\Admin
 */
class Plugin extends AbstractDefault
{
    /**
     * Namespace
     */
    const APP_NAMESPACE = 'laemmi-yourls-admin';

    /**
     * Options
     *
     * @var array
     */
    protected $_options = [
        'allowed_groups' => []
    ];

    /**
     * Permission constants
     */
    const PERMISSION_ACTION_EDIT_COMMENT = 'action-edit-comment';
    const PERMISSION_ACTION_EDIT_LABEL = 'action-edit-label';

    /**
     * Admin permissions
     *
     * @var array
     */
    protected $_adminpermission = [
        self::PERMISSION_ACTION_EDIT_COMMENT,
        self::PERMISSION_ACTION_EDIT_LABEL
    ];

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->startSession();
        parent::__construct($options);
    }

    /**
     * Init
     */
    protected function init()
    {
        $this->initTemplate();
    }

    ####################################################################################################################

    /**
     * Yourls action plugins_loaded
     */
    public function action_plugins_loaded()
    {
        $this->loadTextdomain();
    }

    /**
     * Action: html_head
     *
     * @param array $args
     */
    public function action_html_head(array $args)
    {
        list($context) = $args;

        if('index' === $context) {
            $path = yourls_site_url(false) . '/user/plugins/' . self::APP_NAMESPACE . '/assets/lib/bootstrap/dist';
            echo $this->getCssStyle();
            echo trim('
            <link href="' . $path . '/css/bootstrap.min.css" rel="stylesheet">
            <link href="' . $path . '/css/bootstrap-theme.min.css" rel="stylesheet">
            <script src="' . $path . '/js/bootstrap.min.js"></script>
            ');
        }
    }

    /**
     * Action: admin_page_before_form
     */
    public function action_admin_page_before_form()
    {
        $permissions = $this->helperGetAllowedPermissions();

        $panels = [];
        $panels[] = 'form_new_url-panel-shorturl.twig';
        if(isset($permissions[self::PERMISSION_ACTION_EDIT_COMMENT])) {
            $panels[] = 'form_new_url-panel-comment.twig';
        }
        if(isset($permissions[self::PERMISSION_ACTION_EDIT_LABEL])) {
            $panels[] = 'form_new_url-panel-label.twig';
        }

        echo $this->getTemplate()->render('form_new_url', [
            'nonce_add' => yourls_create_nonce('add_url'),
            'panels' => $panels
        ]);
        ob_start();
    }

    /**
     * Action: admin_page_before_table
     */
    public function action_admin_page_before_table()
    {
        ob_end_clean();
    }

    ####################################################################################################################

    /**
     * Filter: table_head_start
     */
    public function filter_table_head_start()
    {
        $start = '<table id="main_table" class="table table-condensed small tblSorter_"><thead><tr>';

        return $start;
    }

    /**
     * Filter table_add_row_cell_array
     *
     * @return mixed
     */
    public function filter_table_add_row_cell_array()
    {
        list($cells, $keyword, $url, $title, $ip, $clicks, $timestamp) = func_get_args();

        $cells['url']['template'] = preg_replace("/^\<a.*?\<\/small\>/", '<div class="laemmi_url"><a href="%long_url%" title="%long_url%">%long_url_html%</a></div>', $cells['url']['template']);


//        $cells['keyword']['template'] = '<a href="%shorturl%">%shorturl%</a>';
//        $cells['keyword']['template'] = '<a href="%shorturl%">%keyword_html%</a>';

//        'keyword' => array(
//        'template'      => '<a href="%shorturl%">%keyword_html%</a>',
//        'shorturl'      => yourls_esc_url( $shorturl ),
//        'keyword_html'  => yourls_esc_html( $keyword ),
//    ),

        return $cells;
    }

    ####################################################################################################################

    /**
     * Get allowed permissions
     *
     * @return array
     */
    private function helperGetAllowedPermissions()
    {
        if($this->getSession('login', 'laemmi-yourls-easy-ldap')) {
            $inter = array_intersect_key($this->_options['allowed_groups'], $this->getSession('groups', 'laemmi-yourls-easy-ldap'));
            $permissions = [];
            foreach ($inter as $val) {
                foreach ($val as $_val) {
                    $permissions[$_val] = $_val;
                }
            }
        } else {
            $permissions = array_combine($this->_adminpermission, $this->_adminpermission);
        }

        return $permissions;
    }
}