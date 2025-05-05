<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Components;

use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Views\Collection as ViewsCollection;
use Pedstores\Ped\Models\Admin\Pagination as Base;

class Pagination
{
    use Base;
    public const TMP_DIR = 'account/widgets/pagination/';

    public function buildPagination(
        string $baseUrl,
        int $recordCount,
        int $pageLimit
    ): Data {
        $buttonList = new ViewsCollection();
        $this->setLimit($pageLimit);
        $data = $this->getPaginationData(
            $baseUrl,
            $recordCount
        );

        $prev  = $data['prev'];
        $links = $data['links'];
        $next  = $data['next'];

        //Add Prev button
        $buttonList->append(
            new Data(self::TMP_DIR . 'button.php', [
                'parentClassList' => $prev->disabled,
                'parentParams'    => ($prev->disabled ? ' tabindex="-1"' : ''),
                'classList'       => ' px-3',
                'title'           => 'Previous Page',
                'active'          => ($prev->disabled ? false : true),
                'targetPage'      => $prev->url,
                'text'            => 'PREV',
                'mobileText'      => '<'
            ])
        );

        //Add individual page buttons
        foreach ($links as $link) {
            $buttonList->append(
                new Data(self::TMP_DIR . 'button.php', [
                    'parentClassList' => $link->active,
                    'parentParams'    => ($link->active ? ' aria-current="page"' : ''),
                    'classList'       => ' inner px-3' . ($link->active ? ' pe-none' : ''),
                    'title'           => 'Page ' . $link->text,
                    'active'          => ($link->active ? false : true),
                    'targetPage'      => $link->url,
                    'text'            => $link->text,
                    'mobileText'      => $link->text
                ])
            );
        }

        //Add Next button
        $buttonList->append(
            new Data(self::TMP_DIR . 'button.php', [
                'parentClassList' => $next->disabled,
                'parentParams'    => ($next->disabled ? ' tabindex="-1"' : ''),
                'classList'       => ' px-3',
                'title'           => 'Next Page',
                'active'          => ($next->disabled ? false : true),
                'targetPage'      => $next->url,
                'text'            => 'NEXT',
                'mobileText'      => '>',
            ])
        );

        //Return list
        return new Data(self::TMP_DIR . 'list.php', [
            'ariaLabel'  => 'Additional Orders',
            'buttonList' => $buttonList
        ]);
    }
}