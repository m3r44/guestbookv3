<?php

class Pagination
{
    private $results_per_page = 7;
    private $initial_row_limit_pagination = 0;

    public function getConn()
    {
        $conn = new mysqli("localhost", "root", "root", "guestbook");
        return $conn;
    }

    /**
     * @return int
     */
    public function getNumRows()
    {
        $conn = $this->getConn();
        $sql_display = "SELECT * FROM guestbook ORDER BY id DESC";
        $display = $conn->query($sql_display);
        $num_rows = $display->num_rows;

        return $num_rows;
    }

    /**
     * calculates the total no. of pages (finds ceil)
     * @return int
     */
    public function totalNoOfPages(){
        $results_per_page = $this->getResultsPerPage();
        $num_rows = $this->getNumRows();
        $num_of_pages = ceil($num_rows/$results_per_page);
        return $num_of_pages;
    }

    /**
     * retrieves result of getRows() & store into array
     * @return array
     */
    public function getPagination(){
        $results_pagination = $this->getRows();
        while ($results = mysqli_fetch_assoc($results_pagination)) {
            $row[] = $results;
        }
        return $row;
    }

    /**
     * gets last row as last limit
     * @return int|mixed
     */
    public function getRowLimitPagination(){
        $row_limit_pagination = $this->getInitialRowLimitPagination();
        $results_pagination = $this->getRows();
        while ($results = mysqli_fetch_assoc($results_pagination) ) {
            $row_limit_pagination++;    #sets last limit
        }
        return $row_limit_pagination;
    }

    /**
     * returns current page
     * @return int
     */
    public function getCurrent(){
        if (!isset($_GET['page'])){
            $page = 1;
        }
        else{
            $page = $_GET['page'];
        }
        return $page;
    }

    /**
     * gets all rows from database
     * @return bool|mysqli_result
     */
    private function getRows()
    {
        $conn = $this->getConn();
        $page = $this->getCurrent();
        $first_result = $this->getFirstLimit($page);
        $results_per_page = $this->getResultsPerPage();
        $sql_pagination = "SELECT * FROM guestbook ORDER BY id DESC LIMIT " . $first_result . ',' . $results_per_page;
        $results_pagination =$conn->query($sql_pagination);

        return $results_pagination;
    }

    /**
     * gets first row as first limit
     * @param $page
     * @return float|int
     */
    private function getFirstLimit($page){
        $results_per_page = $this->getResultsPerPage();
        $first_result = ($page - 1) * $results_per_page;
        return $first_result;
    }

    /**
     * gets num of results per page
     * @return int
     */
    private function getResultsPerPage(){
        return $this->results_per_page;
    }

    /**
     * gets initial value of row limit pagination
     * @return int
     */
    private function getInitialRowLimitPagination(){
        return $this->initial_row_limit_pagination;
    }
}