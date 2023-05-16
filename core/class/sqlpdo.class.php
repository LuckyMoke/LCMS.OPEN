<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2023-05-16 10:53:13
 * @Description:PDO数据库操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class SQLPDO
{
    private $pdo;
    private $psm;
    private $errorInfo;
    private $errorCode;
    /**
     * @description: 连接数据库
     * @param string $sqlinfo
     * @param string $user
     * @param string $pass
     * @return {*}
     */
    public function __construct($sqlinfo, $user = "", $pass = "")
    {
        try {
            $this->pdo = new PDO($sqlinfo, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        } catch (Exception $e) {
            LCMS::X(500, "数据库-" . iconv('gbk', 'utf-8', $e->getMessage()));
        }

    }
    /**
     * @description: 获取数据库版本
     * @param {*}
     * @return {*}
     */
    public function version()
    {
        return $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
    /**
     * @description: 数据库操作准备
     * @param string $sql
     * @param array $params
     * @return {*}
     */
    public function prepare($sql, $params = [])
    {
        $this->psm = $this->pdo->prepare($sql);
        if ($this->psm) {
            try {
                foreach ($params as $key => $val) {
                    if ($val === "" || $val === null) {
                        $params[$key] = null;
                    }
                }
                $this->psm->execute($params);
                return $this->psm;
            } catch (Exception $e) {
                LCMS::X($this->errno(), $this->error());
            }
        } else {
            LCMS::X(500, "数据错误");
        }
    }
    /**
     * @description: 从结果集中获取下一行
     * @param string $sql
     * @param array $params
     * @param string $result_type
     * @return array|null
     */
    public function fetch($sql, $params = [], $result_type = "")
    {
        return $this->prepare($sql, $params)->fetch($result_type);
    }
    /**
     * @description: 返回一个包含结果集中所有行的数组
     * @param string $sql
     * @param array $params
     * @param string $result_type
     * @return array|null
     */
    public function fetch_all($sql, $params = [], $result_type = "")
    {
        return $this->prepare($sql, $params)->fetchAll($result_type);
    }
    /**
     * @description: 从结果集中的下一行返回单独的一列
     * @param string $sql
     * @param array $params
     * @return {*}
     */
    public function fetch_column($sql, $params = [])
    {
        return $this->prepare($sql, $params)->fetchColumn();
    }
    /**
     * @description: 获取数据库所有表名
     * @param array $sql
     * @return {*}
     */
    public function get_tables($sql = "SHOW TABLES")
    {
        return $this->fetch_all($sql, [], PDO::FETCH_COLUMN);
    }
    /**
     * @description: 查询一条数据
     * @param string $sql
     * @param array $params
     * @param string $result_type
     * @return array|null
     */
    public function get_one($sql, $params = [], $result_type = PDO::FETCH_ASSOC)
    {
        return $this->fetch($sql, $params, $result_type);
    }
    /**
     * @description: 查询多条数据
     * @param string $sql
     * @param array $params
     * @param string $result_type
     * @return array|null
     */
    public function get_all($sql, $params = [], $result_type = PDO::FETCH_ASSOC)
    {
        return $this->fetch_all($sql, $params, $result_type);
    }
    /**
     * @description: 更新数据
     * @param string $sql
     * @param array $params
     * @return {*}
     */
    public function update($sql, $params = [])
    {
        return $this->prepare($sql, $params)->rowCount();
    }
    /**
     * @description: 插入数据
     * @param string $sql
     * @param array $params
     * @return {*}
     */
    public function insert($sql, $params = [])
    {
        return $this->prepare($sql, $params)->rowCount();
    }
    /**
     * @description:
     * @param string $sql
     * @param array $params
     * @return {*}
     */
    public function delete($sql, $params = [])
    {
        return $this->prepare($sql, $params)->rowCount();
    }
    /**
     * @description: 自由查询sql语句
     * @param string $sql
     * @param {*} $result_type
     * @return array
     */
    public function query($sql, $result_type = PDO::FETCH_ASSOC)
    {
        try {
            $this->psm = $this->pdo->query($sql);
        } catch (Exception $e) {
            $this->errorInfo = $e->getMessage();
            $this->errorCode = $e->getCode();
            return;
        }
        if (preg_match('/^(SELECT|SHOW|ANALYZE|OPTIMIZE|CHECK|REPAIR|TRUNCATE).*/i', $sql)) {
            $rows = $this->affected_rows();
            if ($rows > 1) {
                return $this->psm->fetchAll($result_type);
            } elseif ($rows == 1) {
                return $this->psm->fetch($result_type);
            }
        }
    }
    /**
     * @description: 查询数据库条数
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function counter($sql, $params = [])
    {
        return $this->fetch_column($sql, $params);
    }
    /**
     * @description: 获取最后一个插入数据的ID
     * @param {*}
     * @return {*}
     */
    public function insert_id()
    {
        return $this->pdo->lastInsertId();
    }
    /**
     * @description: 返回受 DELETE、INSERT、 或 UPDATE 语句影响的行数
     * @param {*}
     * @return {*}
     */
    public function affected_rows()
    {
        if (gettype($this->psm) === "object") {
            return $this->psm->rowCount();
        }
    }
    /**
     * @description: 返回最后一次操作的错误信息
     * @param {*}
     * @return {*}
     */
    public function error()
    {
        if ($this->errorInfo) {
            $errorInfo       = $this->errorInfo;
            $this->errorInfo = "";
            return $errorInfo;
        }
        if (gettype($this->psm) === "object") {
            $error = $this->psm->errorInfo();
        } else {
            $error = $this->pdo->errorInfo();
        }
        if ($error[0] != 00000 && $error[1]) {
            return "[{$error[1]}] {$error[2]}";
        }
    }
    /**
     * @description: 返回最后一次操作的错误编号
     * @param {*}
     * @return {*}
     */
    public function errno()
    {
        if ($this->errorCode) {
            $errorCode       = $this->errorCode;
            $this->errorCode = "";
            return $errorCode;
        }
        if (gettype($this->psm) === "object") {
            $errno = $this->psm->errorCode();
        } else {
            $errno = $this->pdo->errorCode();
        }
        return $errno;
    }
    /**
     * @description: 关闭数据库连接
     * @param {*}
     * @return {*}
     */
    public function close()
    {
        $this->pdo = null;
        $this->psm = null;
    }
}
