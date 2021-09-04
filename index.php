<?php
    require_once('config.php');
    require_once('CommentHandler.php');
    $commentHandler = new CommentHandler();
    $data = $commentHandler->getComments();
?>

<html>
<head>
    <title>Comment Handler</title>
</head>
<body>
    <h1>Feed</h1>
    <table border="1">
    <tr>
        <td>Name</td>
        <td>Comment</td>
        <td>Replies</td>
    </tr>
    
    <?php
        foreach($data as $comment) {
    ?>
        <tr>
            <td><?=$comment['name']?></td>
            <td><?=$comment['comment']?></td>
            <td>
                <?php
                    if ($comment['reply_count'] > 0) {
                ?>
                    <table border="1">
                        <tr>
                            <td>Name</td>
                            <td>Comment</td>
                            <td>Replies</td>
                        </tr>
                        <?php
                            foreach($comment['replies'] as $secondCommentDepth) {
                        ?>
                            <tr>
                                <td><?=$secondCommentDepth['name']?></td>
                                <td><?=$secondCommentDepth['comment']?></td>
                                <td>
                                <?php
                                    if ($secondCommentDepth['reply_count'] > 0) {
                                ?>
                                    <table border="1">
                                        <tr>
                                            <td>Name</td>
                                            <td>Comment</td>
                                        </tr>
                                        <?php
                                            foreach($secondCommentDepth['replies'] as $thirdCommentDepth) {
                                        ?>
                                            <tr>
                                                <td><?=$thirdCommentDepth['name']?></td>
                                                <td><?=$thirdCommentDepth['comment']?></td>
                                            </tr>
                                        <?php
                                            }
                                        ?>
                                    </table>
                                <?php
                                    } else {
                                ?>
                                    No Replies
                                <?php
                                    }
                                ?>
                                </td>
                            </tr>
                        <?php
                            }
                        ?>
                    </table>
                <?php
                    } else {
                ?>
                    No Replies
                <?php
                    }
                ?>
            </td>
        </tr>
    <?php
        }
    ?>
    </table>
</body>
</html>

