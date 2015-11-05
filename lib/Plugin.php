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
namespace Laemmi\Yourls\Admin;

use Laemmi\Yourls\Plugin\AbstractDefault;

/**
 * Class Plugin
 *
 * @package Laemmi\Yourls\Admin
 */
class Plugin extends AbstractDefault
{
    /**
     * Namespace
     */
    const APP_NAMESPACE = 'laemmi-yourls-admin';


    ####################################################################################################################

    /**
     * Yourls action plugins_loaded
     */
    public function action_plugins_loaded()
    {
//        yourls_load_custom_textdomain(self::LOCALIZED_DOMAIN, realpath(dirname( __FILE__ ) . '/../translations'));
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
            echo $this->getCssStyle();
        }
    }

    /*public function action_admin_page_before_form() {
    ?>

    <div id="new_url2">
        <form id="new_url_form" action="" method="get">
            <fieldset>
                <legend><?php yourls_e('New Short URL'); ?></legend>
                <table>
                    <tr>
                        <td><label><?php yourls_e('Original URL'); ?>:</label></td>
                        <td><input type="text" id="add-url" name="url" class="text" placeholder="http://" /></td>
                        <td rowspan="2"><input type="button" id="add-button" name="add-button" value="<?php yourls_e('Create'); ?>" class="button" style="height:100%" onclick="add_link();" /></td>
                    </tr>
                    <tr>
                        <td><label><?php yourls_e('Short URL'); ?>:</label></td>
                        <td><input type="text" id="add-keyword" class="text" name="keyword" /></td>
                    </tr>
                </table>
            </fieldset>
            <?php yourls_nonce_field( 'add_url', 'nonce-add' ); ?>
        </form>
        <div id="feedback" style="display:none"></div>
        <?php yourls_do_action( 'html_addnew' ); ?>
    </div>

    <?php
    }*/

    ####################################################################################################################

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
}