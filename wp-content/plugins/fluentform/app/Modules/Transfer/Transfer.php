<?php
namespace FluentForm\App\Modules\Transfer;

use Exception;
use FluentForm\App\Services\Transfer\TransferService;
use FluentForm\Framework\Foundation\App;

class Transfer
{

    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request = null;


    public function __construct()
    {
        $this->request = App::getInstance()->make('request');
    }


    /**
     * Export forms as JSON.
     */
    public function exportForms()
    {
        try {
            $formIds = $this->request->get('forms');
            TransferService::exportForms($formIds);
        } catch (Exception $exception) {
            wp_send_json([
                'message' => $exception->getMessage()
            ], 424);
        }
    }

    /**
     * Import forms from a previously exported JSON file.
     */
    public function importForms()
    {
        try {
            $file = $this->request->file('file');
            wp_send_json(TransferService::importForms($file), 200);
        } catch (Exception $exception) {
            wp_send_json([
                'message' => $exception->getMessage()
            ], 424);
        }
    }

    public function exportEntries() {
        try {
            TransferService::exportEntries($this->request->get());
        } catch (Exception $exception) {
            wp_send_json([
                'message' => $exception->getMessage()
            ], 424);
        }
    }

}