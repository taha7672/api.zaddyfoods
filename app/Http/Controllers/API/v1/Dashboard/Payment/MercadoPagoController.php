<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StripeRequest;
use App\Http\Requests\Shop\SubscriptionRequest;
use App\Models\Currency;
use App\Models\Subscription;
use App\Services\PaymentService\MercadoPagoService;
use App\Traits\ApiResponse;
use App\Traits\OnResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use MercadoPago\Config;
use MercadoPago\Item;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Redirect;
use Throwable;

class MercadoPagoController extends Controller
{
    use OnResponse, ApiResponse;

    public function __construct(private MercadoPagoService $service)
    {
        parent::__construct();
    }

    /**
     * process transaction.
     *
     * @param StripeRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function orderProcessTransaction(Request $request): JsonResponse
    {
        SDK::setAccessToken("TEST-6668869803819899-030119-21a35cc741cf11c0619a6eec892e3465-96344171");

        $config = new Config();
        $config->set('sandbox', true); // Устанавливаем режим песочницы (sandbox)
        $config->set('access_token', 'TEST-6668869803819899-030119-21a35cc741cf11c0619a6eec892e3465-96344171'); // Устанавливаем токен доступа

        $item = new Item();
        $item->id = '001'; // Идентификатор товара
        $item->title = 'Название товара'; // Название товара
        $item->quantity = 1; // Количество товара
        $item->unit_price = 10.0; // Цена за единицу товара

        $preference = new Preference();
        $preference->items = array($item); // Добавляем товар в список покупок
        $preference->back_urls = array(
            'success' => "https://foodyman.org/orders/3012",
            'failure' => "https://foodyman.org/orders/3012",
            'pending' => "https://foodyman.org/orders/3012"
        );
        $preference->auto_return = 'approved'; // Автоматический возврат на URL после успешной оплаты

        $preference->save(); // Сохраняем настройки

        $payment_link = $preference->init_point; // Получаем ссылку для оплаты

        dd(
            $preference->Error(),
            $payment_link,
        );

        try {
            $result = $this->service->orderProcessTransaction($request->all());

            return $this->successResponse('success', $result);
        } catch (Throwable $e) {
            $this->error($e);
            return $this->onErrorResponse(['message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)]);
        }

    }

    /**
     * process transaction.
     *
     * @param SubscriptionRequest $request
     * @return JsonResponse
     */
    public function subscriptionProcessTransaction(SubscriptionRequest $request): JsonResponse
    {
        $shop       = auth('sanctum')->user()?->shop ?? auth('sanctum')->user()?->moderatorShop;
        $currency   = Currency::currenciesList()->where('active', 1)->where('default', 1)->first()?->title;

        if (empty($shop)) {
            return $this->onErrorResponse(['message' => __('errors.' . ResponseError::SHOP_NOT_FOUND, locale: $this->language)]);
        }

        if (empty($currency)) {
            return $this->onErrorResponse(['message' => __('errors.' . ResponseError::CURRENCY_NOT_FOUND, locale: $this->language)]);
        }

        try {
            $result = $this->service->subscriptionProcessTransaction($request->all(), $shop, $currency);

            return $this->successResponse('success', $result);
        } catch (Throwable $e) {
            $this->error($e);
            return $this->onErrorResponse(['message' => ResponseError::ERROR_501]);
        }

    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function orderResultTransaction(Request $request): RedirectResponse
    {
        $orderId = (int)$request->input('order_id');

        $to = config('app.front_url') . "orders/$orderId";

        return Redirect::to($to);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function subscriptionResultTransaction(Request $request): RedirectResponse
    {
        $subscription = Subscription::find((int)$request->input('subscription_id'));

        $to = config('app.front_url') . "seller/subscriptions/$subscription->id";

        return Redirect::to($to);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        Log::error('mercado pago', [
            'all'   => $request->all(),
            'reAll' => \request()->all(),
            'input' => @file_get_contents("php://input")
        ]);

//        $status = $request->input('data.object.status');
//
//        $status = match ($status) {
//            'succeeded' => 'paid',
//            default     => 'progress',
//        };
//
//        $token = $request->input('data.object.id');
//
//        $this->service->afterHook($token, $status);
    }

}
