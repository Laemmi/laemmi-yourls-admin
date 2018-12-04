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
 * @author      Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright   ©2015 wdv
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       04.11.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\Plugin\Admin;

use Laemmi\Yourls\Plugin\AbstractDefault;
use Laemmi\Yourls\Paginator;

require_once __DIR__ . '/../../Paginator.php';

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
     * Settings constants
     */
    const SETTING_URL_USER_CREATE = 'laemmi_user_create';
    const SETTING_URL_PROJECTS = 'laemmi_projects';

    /**
     * Permission constants
     */
    const PERMISSION_ACTION_EDIT_COMMENT = 'action-edit-comment';
    const PERMISSION_ACTION_EDIT_LABEL = 'action-edit-label';
    const PERMISSION_ACTION_ADD_PROJECT = 'action-add-project';
    const PERMISSION_ACTION_ADD_OTHER_PROJECT = 'action-add-other-project';
    const PERMISSION_ACTION_EDIT_OTHER = 'action-edit-other';
    const PERMISSION_ACTION_ADD = 'action-add';

    /**
     * Admin permissions
     *
     * @var array
     */
    protected $_adminpermission = [
        self::PERMISSION_ACTION_EDIT_COMMENT,
        self::PERMISSION_ACTION_EDIT_LABEL,
        self::PERMISSION_ACTION_ADD_PROJECT,
        self::PERMISSION_ACTION_ADD_OTHER_PROJECT,
        self::PERMISSION_ACTION_EDIT_OTHER,
        self::PERMISSION_ACTION_ADD,
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
            echo $this->getBootstrap();
            echo $this->getCssStyle();
        }
    }

    /**
     * Action: admin_page_before_content
     */
    public function action_admin_page_before_content()
    {
        echo '<div id="PageStats">';
    }

    /**
     * Action: admin_page_before_form
     */
    public function action_admin_page_before_form()
    {
        $panels = [];
        $panels[] = 'form_new_url-panel-shorturl.twig';

        if($this->_hasPermission(self::PERMISSION_ACTION_ADD_PROJECT)) {
            $panels[] = 'form_new_url-panel-project.twig';
        }

        if($this->_hasPermission(self::PERMISSION_ACTION_EDIT_COMMENT)) {
            $panels[] = 'form_new_url-panel-comment.twig';
        }
        if($this->_hasPermission(self::PERMISSION_ACTION_EDIT_LABEL)) {
            $panels[] = 'form_new_url-panel-label.twig';
        }

        $projectlist = [];
        foreach($this->_options['projectlist'] as $key => $val) {
            if($this->_hasPermission(self::PERMISSION_ACTION_ADD_OTHER_PROJECT) || $this->_hasPermission(self::PERMISSION_ACTION_ADD, [$key])) {
                $projectlist[$key] = $key;
            }
        }

        $projects = $this->getSession('projects', 'wdv-yourls-bind-user-to-entry');

        echo '</div>';
        echo $this->getTemplate()->render('form_new_url', [
            'nonce_add' => yourls_create_nonce('add_url'),
            'panels' => $panels,
            'projectlist' => $projectlist,
            'projectlist_value' => $projects?$projects:$projectlist,
            'shorturl_charset' => yourls_get_shorturl_charset()
        ]);

        global $is_bookmark, $return;
        // If bookmarklet, add message. Otherwise, hide hidden share box.
        if ( !$is_bookmark ) {
            yourls_share_box( '', '', '', '', '', '', true );
        } else {
            echo '<script>$(document).ready(function(){
                feedback("' . addslashes($return['message']) . '", "'. $return['status'] .'");
                init_clipboard();
            });</script>';
        }

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
     * Yourls filter table_add_row_action_array
     *
     * @param $data
     * @return array
     */
    public function filter_table_add_row_action_array()
    {
        global $url_result, $keyword;

        list($actions) = func_get_args();

        if(! isset($url_result)) {
            return array();
        }

        if(! $this->_hasPermission(self::PERMISSION_ACTION_EDIT_OTHER)) {
            if (!$this->_hasPermission(self::PERMISSION_ACTION_EDIT_COMMENT, $url_result->{self::SETTING_URL_PROJECTS}) || !$this->_hasPermission(self::PERMISSION_ACTION_EDIT_LABEL, $url_result->{self::SETTING_URL_PROJECTS})) {
                if ($url_result->{self::SETTING_URL_USER_CREATE} && YOURLS_USER !== $url_result->{self::SETTING_URL_USER_CREATE}) {
                    unset($actions['wdv_edit_comment_label']);
                }
            }
        }

        return $actions;
    }

    /**
     * Filter: table_head_start
     */
    public function filter_table_head_start()
    {
        ob_start();
    }

    private $_string_main_table_head = '';

    /**
     * Filter: table_head_end
     */
    public function filter_table_head_end()
    {
        $this->_string_main_table_head = ob_get_contents();
        ob_end_clean();
        ob_start();
    }

    /**
     * Action: html_tfooter
     */
    public function action_html_tfooter()
    {
        global $params;
        ob_end_clean();

        $projectlist = [];
        foreach($this->_options['projectlist'] as $key => $val) {
            if($this->_hasPermission(self::PERMISSION_ACTION_ADD_OTHER_PROJECT) || $this->_hasPermission('admin', [$key])) {
                $projectlist[$key] = $key;
            }
        }

        $projectlist = array_merge([
            null => yourls__('All projects', self::APP_NAMESPACE),
        ], $projectlist);

        $select = [
            'search_in' => array(
                'all'     => yourls__('All fields', self::APP_NAMESPACE),
                'keyword' => yourls__('Short URL', self::APP_NAMESPACE),
                'url'     => yourls__('URL', self::APP_NAMESPACE),
                'title'   => yourls__('Title', self::APP_NAMESPACE),
                'ip'      => yourls__('IP', self::APP_NAMESPACE),
            ),
            'sort_by' => array(
                'keyword'      => yourls__('Short URL', self::APP_NAMESPACE),
                'url'          => yourls__('URL', self::APP_NAMESPACE),
                'timestamp'    => yourls__('Date', self::APP_NAMESPACE),
                'ip'           => yourls__('IP', self::APP_NAMESPACE),
                'clicks'       => yourls__('Clicks', self::APP_NAMESPACE),
            ),
            'sort_order' => array(
                'asc'  => yourls__('Ascending', self::APP_NAMESPACE),
                'desc' => yourls__('Descending', self::APP_NAMESPACE),
            ),
            'click_filter' => array(
                'more' => yourls__('more', self::APP_NAMESPACE),
                'less' => yourls__('less', self::APP_NAMESPACE),
            ),
            'date_filter' => array(
                'before'  => yourls__('before', self::APP_NAMESPACE),
                'after'   => yourls__('after', self::APP_NAMESPACE),
                'between' => yourls__('between', self::APP_NAMESPACE),
            ),

            'projectlist' => $projectlist,
        ];

        $params['projectlist'] = $this->getRequest('projectlist');

        $paginator = new Paginator();
        $paginator
            ->setCurrentPageNumber($params['page'])
            ->setTotalPagesNumber($params['total_pages'])
            ->setUrlParams($params)
            ->go();
        $pagination_template = $this->getTemplate()->render('paginator', [
            'paginator' => $paginator
        ]);

        echo $this->getTemplate()->render('table_head', [
            'main_table_head'       => $this->_string_main_table_head,
            'select'                => $select,
            'search_text'           => yourls_esc_attr($params['search_text']),
            'perpage'               => isset($params['perpage'])?$params['perpage']:'',
            'click_limit'           => isset($params['click_limit'])?$params['click_limit']:'',
            'date_first'            => isset($params['date_first'])?$params['date_first']:'',
            'date_second'           => isset($params['date_second'])?$params['date_second']:'',

            'search_in'             => isset($params['search_in'])?$params['search_in']:'',
            'sort_by'               => isset($params['sort_by'])?$params['sort_by']:'',
            'sort_order'            => isset($params['sort_order'])?$params['sort_order']:'',
            'click_filter'          => isset($params['click_filter'])?$params['click_filter']:'',
            'date_filter'           => isset($params['date_filter'])?$params['date_filter']:'',
            'projectlist_value'     => $this->getRequest('projectlist'),
            'pagination'            => $pagination_template,
            'p_pages_total'         => sprintf(yourls_n('1 page', '%s pages', $params['total_pages']), $params['total_pages']),
        ]);

        echo $this->getTemplate()->render('table_foot', [
            'pagination'            => $pagination_template
        ]);
    }

    /**
     * Filter table_add_row_cell_array
     *
     * @return mixed
     */
    public function filter_table_add_row_cell_array()
    {
        list($cells, $keyword, $url, $title, $ip, $clicks, $timestamp) = func_get_args();

        $cells['url']['template'] = preg_replace("/^\<a.*?\<\/small\>/", '<div class="wdv_url"><a href="%long_url%" title="%long_url%">%long_url_html%</a></div>', $cells['url']['template']);

        unset($cells['ip']);

        return $cells;
    }

    /**
     * Filter: admin_links
     */
    public function filter_admin_links()
    {
        list($admin_links) = func_get_args();

        $admin_links['admin']['anchor'] = yourls__('Home', self::APP_NAMESPACE);

        return $admin_links;
    }

    public function filter_table_head_cells()
    {
        list($arr) = func_get_args();

        unset($arr['ip']);

        return $arr;
    }

    ####################################################################################################################

    /**
     * Get allowed permissions
     *
     * @param array $projects
     * @return array
     */
    protected function helperGetAllowedPermissions(array $projects = array())
    {
        if($this->getSession('login', 'wdv-yourls-easy-ldap')) {
            $inter = array_intersect_key($this->_options['allowed_groups'], $this->getSession('groups', 'wdv-yourls-easy-ldap'));

            if($projects) {
                $inter2 = array();
                foreach ($projects as $val) {
                    foreach ($this->_options['projectlist'] as $_key => $_val) {
                        if ($val === $_key) {
                            $interX = array_intersect_key($_val, $this->getSession('groups', 'wdv-yourls-easy-ldap'));
                            $inter2 = array_merge($inter2, $interX);
                        }
                    }
                }
                $inter = $inter2;
            }

            $permissions = [];
            foreach ($inter as $key => $val) {
                foreach ($val as $_val) {
                    $permissions[$_val] = $_val;
                }
            }
        } else {
            $permissions = array_combine($this->_adminpermission, $this->_adminpermission);
        }

        return $permissions;
    }

    /**
     * Has permission to right
     *
     * @param $permission
     * @param string $projects
     * @return bool
     */
    protected function _hasPermission($permission, $projects = '')
    {
        if(!is_array($projects)) {
            $projects = (array)@json_decode($projects, true);
        }

        $permissions = $this->helperGetAllowedPermissions($projects);

        return isset($permissions[$permission]);
    }
}