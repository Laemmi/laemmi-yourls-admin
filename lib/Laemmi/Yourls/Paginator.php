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
 * @package     Paginator.php
 * @author      Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright   ©2016 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     2.7.0
 * @since       22.02.16
 */

namespace Laemmi\Yourls;


class Paginator implements \Iterator
{
    /**
     * Current page
     *
     * @var int
     */
    protected $_current_page = 0;

    /**
     * Total pages
     *
     * @var int
     */
    protected $_total_pages = 0;

    /**
     * First page
     *
     * @var int
     */
    protected $_first_page = 0;

    /**
     * Last page
     *
     * @var int
     */
    protected $_last_page = 0;

    /**
     * Url parameter
     *
     * @var array
     */
    protected $_url_params = [];

    public function go()
    {
        $this->_first_page = max(min($this->_total_pages - 4, $this->_current_page - 2), 1);
        $this->_last_page = min(max(5, $this->_current_page + 2), $this->_total_pages);

        for($i = $this->_first_page ; $i <= $this->_last_page; $i++) {
            $this->array[] = $i;
        }
    }

    ####################################################################################################################

    private $position = 0;
    private $array = [];

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    ####################################################################################################################

    /**
     * Set current page number
     *
     * @param $value
     * @return $this
     */
    public function setCurrentPageNumber($value)
    {
        $this->_current_page = (int) $value;
        return $this;
    }

    /**
     * Set total page number
     *
     * @param $value
     * @return $this
     */
    public function setTotalPagesNumber($value)
    {
        $this->_total_pages = (int) $value;
        return $this;
    }

    /**
     * Set parameter for url
     *
     * @param array $params
     * @return $this
     */
    public function setUrlParams(array $params = [])
    {
        $this->_url_params = $params;
        return $this;
    }

    /**
     * Get fist page
     *
     * @return bool|mixed
     */
    public function firstPage()
    {
        if($this->_first_page >= 2) {
            return 1;
        }

        return false;
    }

    /**
     * Get last page
     *
     * @return bool|mixed
     */
    public function lastPage()
    {
        if($this->_last_page < $this->_total_pages) {
            return $this->_total_pages;
        }

        return false;
    }

    /**
     * Get next page
     *
     * @return bool|int
     */
    public function nextPage()
    {
        $value = $this->_current_page + 1;

        if($value <= $this->_total_pages) {
            return $value;
        }

        return false;
    }

    /**
     * Get previous page
     *
     * @return bool|int
     */
    public function previousPage()
    {
        $value = $this->_current_page - 1;

        if($value > 0) {
            return $value;
        }

        return false;
    }

    /**
     * Get current page number
     *
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->_current_page;
    }

    /**
     * Get total pages number
     *
     * @return int
     */
    public function getTotalPagesNumber()
    {
        return $this->_total_pages;
    }

    /**
     * Get url
     *
     * @param $page
     * @return mixed
     */
    public function url($page)
    {
        return yourls_add_query_arg(array_merge($this->_url_params, ['page' => $page]), yourls_admin_url('index.php'));
    }
}