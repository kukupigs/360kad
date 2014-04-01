<?php

!defined('IN_TIPASK') && exit('Access Denied');

class messagecontrol extends base {

    function messagecontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('user');
        $this->load("message");
    }

    /**
     * 私人消息
     */
    function onpersonal() {
        $navtitle = '个人消息';
        $type = 'personal';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $messagelist = $_ENV['message']->group_by_touid($this->user['uid'], $startindex, $pagesize);
        $messagenum = $_ENV['message']->rownum_by_touid($this->user['uid']);
        $departstr = page($messagenum, $pagesize, $page, "message/personal");
        include template("message");
    }

    /**
     * 系统消息
     */
    function onsystem() {
        $navtitle = '系统消息';
        $type = 'system';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $_ENV['message']->read_by_fromuid(0);
        $messagelist = $_ENV['message']->list_by_touid($this->user['uid'], $startindex, $pagesize);
        $messagenum = $this->db->fetch_total('message', 'touid=' . $this->user['uid'] . ' AND fromuid=0 AND status<>2 ');
        $departstr = page($messagenum, $pagesize, $page, "message/system");
        include template("message");
    }

    /* 发消息 */

    function onsend() {
        $navtitle = '发站内消息';
        $sendto = $_ENV['user']->get_by_uid(intval($this->get[2]));
        if (isset($this->post['submit'])) {
            $touser = $_ENV['user']->get_by_username($this->post['username']);
            (!$touser) && $this->message('该用户不存在!', "message/send");
            ($touser['uid'] == $this->user['uid']) && $this->message("不能给自己发消息!", "message/send");
            (trim($this->post['content']) == '') && $this->message("消息内容不能为空!", "message/send");
            $_ENV['message']->add($this->user['username'], $this->user['uid'], $touser['uid'], $this->post['subject'], $this->post['content']);
            $this->credit($this->user['uid'], $this->setting['credit1_message'], $this->setting['credit2_message']);
            $this->message('消息发送成功!', get_url_source());
        }
        include template('sendmsg');
    }

    /* 查看消息 */

    function onview() {
        $navtitle = "查看消息";
        $type = ($this->get[2] == 'personal') ? 'personal' : 'system';
        $fromuid = intval($this->get[3]);
        $page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $_ENV['message']->read_by_fromuid($fromuid);
        $fromuser = $_ENV['user']->get_by_uid($fromuid);
        $status = 1;
        $messagelist = $_ENV['message']->list_by_fromuid($fromuid, $startindex, $pagesize);
        $messagenum = $this->db->fetch_total('message', "fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->user['uid'] . ") AND status IN (0,1)) OR ((touid=" . $this->user['uid'] . " AND fromuid=" . $fromuid . ") AND  status IN (0,2))");
        $departstr = page($messagenum, $pagesize, $page, "message/view/$type/$fromuid");
        include template('viewmessage');
    }

    /* 删除消息 */

    /**
     * 对于消息状态 status = 0  消息在两者都没有删除，1代表被发消息者删除，2代表被收件人删除
     */
    function onremove() {
        if (isset($this->post['submit'])) {
            $inbox = $this->post['messageid']['inbox'];
            $outbox = $this->post['messageid']['outbox'];
            if ($inbox)
                $_ENV['message']->remove("inbox", $inbox);

            if ($outbox)
                $_ENV['message']->remove("outbox", $outbox);

            $this->message("消息删除成功!", get_url_source());
        }
    }

    /**
     * 删除对话
     */
    function onremovedialog() {
        if($this->post['message_author']){
            $authors = $this->post['message_author'];
            $_ENV['message']->remove_by_author($authors);
             $this->message("对话删除成功!", get_url_source());
        }
    }

}

?>