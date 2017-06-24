<?php

namespace DDPro\Admin\Http\Controllers;

use DDPro\Admin\Config\Factory as ConfigFactory;
use DDPro\Admin\Includes\CustomMultup;
use DDPro\Admin\Includes\UploadedImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Session\SessionManager as Session;
use Illuminate\View\View;
use Log;

/**
 * DDPro Admin Controller
 *
 * This is the main controller class for all DDPro Admin requests.
 * It handle for Dashboard, Utility and Settings pages
 *
 * ### Entry Points
 *
 * The entry points for each function are documented in the function docblocks.
 * See also `php artisan route:list`.
 */
class AdminController extends Controller
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * @var View
     */
    protected $view;

    /**
     * Class constructor
     *
     * @param \Illuminate\Http\Request              $request
     * @param \Illuminate\Session\SessionManager    $session
     */
    public function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Dashboard and Utility (file download, page) routes.
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Shows the dashboard page
     *
     * * **route method**: GET
     * * **route name**: admin_dashboard
     * * **route URL**: admin
     *
     * @return Response
     */
    public function dashboard()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'administrator dashboard');
        // if the dev has chosen to use a dashboard
        if (config('administrator.use_dashboard')) {

            // set the view for the dashboard
            $viewname = config('administrator.dashboard_view');
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'administrator dashboard view == ' . $viewname);
            $this->view = view($viewname);

            return $this->view;

            // else we should redirect to the menu item
        } else {
            /** @var ConfigFactory $configFactory */
            $configFactory = app('admin_config_factory');
            $home          = config('administrator.home_page');

            // first try to find it if it's a model config item
            $config = $configFactory->make($home);

            if (! $config) {
                throw new \InvalidArgumentException("Administrator: " .  trans('administrator::administrator.valid_home_page'));
            } elseif ($config->getType() === 'model') {
                return redirect()->route('admin_index', [$config->getOption('name')]);
            } elseif ($config->getType() === 'settings') {
                return redirect()->route('admin_settings', [$config->getOption('name')]);
            }
        }
    }

    /**
     * The GET method that runs when a user needs to download a file
     *
     * * **route method**: GET
     * * **route name**: admin_file_download
     * * **route URL**: admin/file_download
     *
     * @return Response
     */
    public function fileDownload()
    {
        if ($response = $this->session->get('administrator_download_response')) {
            $this->session->forget('administrator_download_response');
            $filename = substr($response['headers']['content-disposition'][0], 22, -1);

            return response()->download($response['file'], $filename, $response['headers']);
        } else {
            return redirect()->back();
        }
    }

    /**
     * The POST method that runs when a user needs to upload a file
     *
     * This is normally triggered using drag and drop or copy and paste uploads from
     * within the wysiwyg editor (ckeditor).
     *
     * * **route method**: GET/POST
     * * **route name**: admin_file_upload
     * * **route URL**: admin/file_upload
     *
     * @return Response
     * @link http://docs.ckeditor.com/#!/guide/dev_file_upload
     * @link http://ckeditor.com/addon/uploadimage
     */
    public function fileUpload()
    {
        // in CKEditor the file is sent as 'upload'
        /** @var CustomMultup $multup */
        $multup = CustomMultup::open(
            'upload',
            null,
            'editor',
            true
        );

        /** @var array $result */
        $result = $multup->upload();
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Upload image result: ' . print_r($result, true));

        /** @var UploadedImage $upload */
        $upload = $result[0];

        // Assemble the response needed by ckeditor
        $response = [
            'uploaded'      => 1,
            'fileName'      => $upload->filename,
            'url'           => $upload->url,
        ];
        return response()->json($response);
    }

    /**
     * The pages view
     *
     * This is where the custom view pages are handled.  These allow the caller to preserve the
     * navigation of Administrator, but have you complete control over the content section. For
     * these you simply need to prefix `page.` to your view path and pass that to the menu array
     * in the configuration (administrator.menu).
     *
     * * **route method**: GET
     * * **route name**: admin_page
     * * **route URL**: admin/page/{page}
     *
     * @return Response
     */
    public function page($page)
    {
        // set the page
        $this->view = view($page);

        return $this->view;
    }

    /**
     * POST method for switching a user's locale
     *
     * * **route method**: POST
     * * **route name**: admin_switch_locale
     * * **route URL**: admin/switch_locale/{locale}
     *
     * @param string	$locale
     *
     * @return RedirectResponse
     */
    public function switchLocale($locale)
    {
        if (in_array($locale, config('administrator.locales'))) {
            $this->session->put('administrator_locale', $locale);
        }

        return redirect()->back();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Settings routes
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * The main view for any of the settings pages
     *
     * @param string	$settingsName
     *
     * @return Response
     */
    public function settings($settingsName)
    {
        // set the layout content and title
        $this->layout->content = view("administrator::settings");

        return $this->layout;
    }

    /**
     * POST save settings method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
     *
     * @return string JSON
     */
    public function settingsSave()
    {
        $config = app('itemconfig');
        $save   = $config->save($this->request, app('admin_field_factory')->getEditFields());

        if (is_string($save)) {
            return response()->json([
                'success' => false,
                'errors'  => $save,
            ]);
        } else {
            // override the config options so that we can get the latest
            app('admin_config_factory')->updateConfigOptions();

            return response()->json([
                'success' => true,
                'data'    => $config->getDataModel(),
                'actions' => app('admin_action_factory')->getActionsOptions(),
            ]);
        }
    }

    /**
     * POST method for handling custom actions on the settings page
     *
     * @param string	$settingsName
     *
     * @return string JSON
     */
    public function settingsCustomAction($settingsName)
    {
        $config        = app('itemconfig');
        $actionFactory = app('admin_action_factory');
        $actionName    = $this->request->input('action_name', false);

        // get the action and perform the custom action
        $action = $actionFactory->getByName($actionName);
        $data   = $config->getDataModel();
        $result = $action->perform($data);

        // override the config options so that we can get the latest
        app('admin_config_factory')->updateConfigOptions();

        // if the result is a string, return that as an error.
        if (is_string($result)) {
            return response()->json(['success' => false, 'error' => $result]);

            // if it's falsy, return the standard error message
        } elseif (! $result) {
            $messages = $action->getOption('messages');

            return response()->json(['success' => false, 'error' => $messages['error']]);
        } else {
            $response = ['success' => true, 'actions' => $actionFactory->getActionsOptions(true)];

            // if it's a download response, flash the response to the session and return the download link
            if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse')) {
                $file    = $result->getFile()->getRealPath();
                $headers = $result->headers->all();
                $this->session->put('administrator_download_response', ['file' => $file, 'headers' => $headers]);

                $response['download'] = route('admin_file_download');

                // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
            } elseif (is_a($result, '\Illuminate\Http\RedirectResponse')) {
                $response['redirect'] = $result->getTargetUrl();
            }

            return response()->json($response);
        }
    }
}
