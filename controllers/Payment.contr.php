<?php 

	namespace Controllers {

		use Models\Payment as PaymentModel;
		use Views\View as View;

		class Payment extends Controller  {
			
			public static function POST_create() {
				$data = self::$request->body;
				if (!self::check_data($data, ['reference_id', 'months'])) View::bad_request();

				$plan = PaymentModel::get_plan($data->reference_id);
				if (!$plan) View::bad_request();

				if ($data->months == 0 || $data->months > 12) View::bad_request();
				$plan['price'] = $plan['price'] * $data->months;

				$paypal = new \PayPal();
				$paypal_config = [
					"intent" => "CAPTURE",
					"purchase_units" => [
						[	
							"reference_id" => $plan['reference_id'],
							"amount" => [
								"value" => $plan['price'],
								"currency_code" => $plan['currency_code']
							],
						]
					],
					"application_context" => [
						"cancel_url" => $paypal->cancel_url,
						"return_url" => $paypal->return_url
					] 
				];

				$result = $paypal->create_order($paypal_config);
				$approve_url = preg_replace('/({token})/', $result->id, $paypal->approve_url);
				$return = [
					"id" => $result->id,
					"approve" => $approve_url,
					"status" => $result->status
				];

				View::response($return);
			}

			public static function GET_capture() {
				$token = self::$request->querys['token'] ?? View::bad_request();
				$paypal = new \PayPal();
				$capture = $paypal->capture_order($token);

				if (PaymentModel::check_payment($token)) View::throw_error('payment_already_captured');
				if (isset($capture->name) AND $capture->name == "UNPROCESSABLE_ENTITY") {
					View::throw_error('payment_error');
				}
				if ($capture->status !== "COMPLETED") View::throw_error('payment_error');

				$capture_ = $capture->purchase_units[0]->payments->captures[0];
				$reference_id = $capture->purchase_units[0]->reference_id;
				$base_price = PaymentModel::get_plan($reference_id)['price'];
				$duration = $capture_->amount->value / $base_price;
				$expire_date = strtotime("+$duration months");
				$expire_date = date("Y-m-d H:i:s", $expire_date);

				$payment = [
					'payment_id' => $capture->id,
					'capture_id' => $capture_->id,
					'master_id' => self::$user->master_id,
					'payment_amount' => $capture_->amount->value,
					'payment_currency' => $capture_->amount->currency_code,
					'account_type' => 'premium',
					'payment_time' => date('Y-m-d h:i:s'),
					'full_name' => $capture->payer->name->given_name ." ". $capture->payer->name->surname,
					'payer_email' => $capture->payer->email_address,
					'payer_id' => $capture->payer->payer_id
				];

				$active_plan = [
					'master_id' => self::$user->master_id,
					'payment_id' => $capture->id,
					'reference_id' => $reference_id,
					'duration' => $duration,
					'start_date' => date('Y-m-d h:i:s'),
					'expire_date' => $expire_date
				];

				PaymentModel::create_payment($payment, $active_plan);
				self::$user->account_type = "premium";
				self::$user->new_jwt();
				$info = self::$user->info;
				View::response($info);
			}

			public static function GET_history() {
				$response = PaymentModel::get_history(self::$user->master_id);
				View::response($response);
			}
		}
	}
