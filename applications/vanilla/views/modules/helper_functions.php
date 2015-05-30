<?php if (!defined('APPLICATION')) exit();

if (!function_exists('WriteModuleDiscussion')):
    function WriteModuleDiscussion($Discussion, $Px = 'Bookmark') {
        ?>
        <li id="<?php echo "{$Px}_{$Discussion->DiscussionID}"; ?>" class="<?php echo CssClass($Discussion); ?>">
   <span class="Options">
      <?php
      //      echo OptionsList($Discussion);
      echo BookmarkButton($Discussion);
      ?>
   </span>

            <div class="Title"><?php
                echo Anchor(Gdn_Format::Text($Discussion->Name, FALSE), DiscussionUrl($Discussion).($Discussion->CountCommentWatch > 0 ? '#Item_'.$Discussion->CountCommentWatch : ''), 'DiscussionLink');
                ?></div>
            <div class="Meta">
                <?php
                $Last = new stdClass();
                $Last->UserID = $Discussion->LastUserID;
                $Last->Name = $Discussion->LastName;

                echo NewComments($Discussion);

                echo '<span class="MItem">'.Gdn_Format::Date($Discussion->LastDate, 'html').UserAnchor($Last).'</span>';
                ?>
            </div>
        </li>
    <?php
    }
endif;

if (!function_exists('WritePromotedContent')):
    /**
     * Generates html output of $Content array
     *
     * @param array|object $Content
     * @param PromotedContentModule $Sender
     */
    function WritePromotedContent($Content, $Sender) {
        static $UserPhotoFirst = NULL;
        if ($UserPhotoFirst === NULL)
            $UserPhotoFirst = C('Vanilla.Comment.UserPhotoFirst', TRUE);

        $ContentType = val('RecordType', $Content);
        $ContentID = val("{$ContentType}ID", $Content);
        $Author = val('Author', $Content);
        $ContentURL = val('Url', $Content);
        $Sender->EventArguments['Content'] = &$Content;
        $Sender->EventArguments['ContentUrl'] = &$ContentURL;
        ?>
        <div id="<?php echo "Promoted_{$ContentType}_{$ContentID}"; ?>" class="<?php echo CssClass($Content); ?>">
            <div class="AuthorWrap">
         <span class="Author">
            <?php
            if ($UserPhotoFirst) {
                echo UserPhoto($Author);
                echo UserAnchor($Author, 'Username');
            } else {
                echo UserAnchor($Author, 'Username');
                echo UserPhoto($Author);
            }
            $Sender->FireEvent('AuthorPhoto');
            ?>
         </span>
         <span class="AuthorInfo">
            <?php
            echo ' '.WrapIf(htmlspecialchars(val('Title', $Author)), 'span', array('class' => 'MItem AuthorTitle'));
            echo ' '.WrapIf(htmlspecialchars(val('Location', $Author)), 'span', array('class' => 'MItem AuthorLocation'));
            $Sender->FireEvent('AuthorInfo');
            ?>
         </span>
            </div>
            <div class="Meta CommentMeta CommentInfo">
         <span class="MItem DateCreated">
            <?php echo Anchor(Gdn_Format::Date($Content['DateInserted'], 'html'), $ContentURL, 'Permalink', array('rel' => 'nofollow')); ?>
         </span>
                <?php
                // Include source if one was set
                if ($Source = GetValue('Source', $Content))
                    echo Wrap(sprintf(T('via %s'), T($Source.' Source', $Source)), 'span', array('class' => 'MItem Source'));

                $Sender->FireEvent('ContentInfo');
                ?>
            </div>
            <div
                class="Title"><?php echo Anchor(Gdn_Format::Text(SliceString($Content['Name'], $Sender->TitleLimit), FALSE), $ContentURL, 'DiscussionLink'); ?></div>
            <div class="Body">
                <?php
                echo Anchor(strip_tags(Gdn_Format::To(SliceString($Content['Body'], $Sender->BodyLimit), $Content['Format'])), $ContentURL, 'BodyLink');
                $Sender->FireEvent('AfterPromotedBody'); // separate event to account for less space.
                ?>
            </div>
        </div>
    <?php
    }
endif;

if (!function_exists('writePromotedContentList')):
    /**
     * Generate a modern view of array $data.
     *
     * @param array $data The data used to generate the view
     */
    function writePromotedContentList($data) {
        ?>
        <ul class="PromotedContentList DataList">
            <?php foreach ($data as $row) {
                writePromotedContentRow($row, 'modern');
            } ?>
        </ul>
    <?php
    }
endif;

if (!function_exists('writePromotedContentTable')):
    /**
     * Generate a table view of array $data.
     *
     * @param array $data The $data used to generate the view
     */
    function writePromotedContentTable($data) {
        ?>
        <div class="DataTableContainer">
            <div class="DataTableWrap">
                <table class="DataTable">
                    <thead>
                    <tr>
                        <td class="DiscussionName">
                            <div class="Wrap"><?php echo T('Subject'); ?></div>
                        </td>
                        <td class="BlockColumn BlockColumn-User LastUser">
                            <div class="Wrap"><?php echo T('Author'); ?></div>
                        </td>
                    </tr>
                    </thead>
                    <?php foreach ($data as $row) {
                        writePromotedContentRow($row, 'table');
                    } ?>
                </table>
            </div>
        </div>
    <?php
    }
endif;

if (!function_exists('writePromotedContentRow')):
    /**
     * Write a promoted content item in a table or modern view.
     *
     * @param array $row The row to output.
     * @param string $view The view to use.
     */
    function writePromotedContentRow($row, $view) {
        $title = htmlspecialchars(val('Name', $row));
        $url = val('Url', $row);
        $body = Gdn_Format::PlainText(val('Body', $row), val('Format', $row));
        $categoryUrl = val('CategoryUrl', $row);
        $categoryName = val('CategoryName', $row);
        $date = val('DateUpdated', $row) ?: val('DateInserted', $row);
        $date = Gdn_Format::Date($date, 'html');
        $type = val('RecordType', $row, 'post');
        $id = val('CommentID', $row, val('DiscussionID', $row, ''));
        $author = val('Author', $row);
        $username = val('Name', $author);
        $userUrl = val('Url', $author);
        $userPhoto = val('PhotoUrl', $author);
        $cssClass = val('CssClass', $author);

        if ($view == 'table') {
            ?>
            <tr id="Promoted_<?php echo $type.'_'.$id; ?>" class="Item PromotedContent-Item <?php echo $cssClass; ?>">
                <td class="Name">
                    <div class="Wrap">
                        <a class="Title" href="<?php echo $url; ?>">
                            <?php echo $title; ?>
                        </a>
                        <span class="MItem Category"><?php echo T('in'); ?> <a href="<?php echo $categoryUrl; ?>"
                                                                               class="MItem-CategoryName"><?php echo $categoryName; ?></a></span>

                        <div class="Description"><?php echo $body; ?></div>
                    </div>
                </td>
                <td class="BlockColumn BlockColumn-User User">
                    <div class="Block Wrap">
                        <a class="PhotoWrap PhotoWrapSmall" href="<?php echo $userUrl; ?>">
                            <img class="ProfilePhoto ProfilePhotoSmall" src="<?php echo $userPhoto; ?>">
                        </a>
                        <a class="UserLink BlockTitle" href="<?php echo $userUrl; ?>"><?php echo $username; ?></a>

                        <div class="Meta">
                            <a class="CommentDate MItem" href="<?php echo $url; ?>"><?php echo $date; ?></a>
                        </div>
                    </div>
                </td>
            </tr>

        <?php } else { ?>

            <li id="Promoted_<?php echo $type.'_'.$id; ?>" class="Item PromotedContent-Item <?php echo $cssClass; ?>">
                <?php if (C('EnabledPlugins.IndexPhotos')) { ?>
                    <a title="<?php echo $username; ?>" href="<?php echo $userUrl; ?>" class="IndexPhoto PhotoWrap">
                        <img src="<?php echo $userPhoto; ?>" alt="<?php echo $username; ?>"
                             class="ProfilePhoto ProfilePhotoMedium">
                    </a>
                <?php } ?>
                <div class="ItemContent Discussion">
                    <div class="Title">
                        <a href="<?php echo $url; ?>">
                            <?php echo $title; ?>
                        </a>
                    </div>
                    <div class="Excerpt"><?php echo $body; ?></div>
                    <div class="Meta">
                        <span class="MItem DiscussionAuthor"><ahref="<?php echo $userUrl; ?>
                            "><?php echo $username; ?></a></span>
                        <span class="MItem Category"><?php echo T('in'); ?> <a href="<?php echo $categoryUrl; ?>"
                                                                               class="MItem-CategoryName"><?php echo $categoryName; ?></a></span>
                    </div>
                </div>
            </li>

        <?php }
    }
endif;
