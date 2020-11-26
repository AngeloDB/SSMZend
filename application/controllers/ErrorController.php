<?php

	class ErrorController extends Zend_Controller_Action {

		public function errorAction() {
			$errors = $this->_getParam('error_handler');
			$msgbox1 = '<div>
				<div style="border-bottom:1px dashed #888;font-size:1em;padding-bottom:8px;">
					<div style="border:2px solid yellow;">
						<div style="border:3px solid #EEE;text-align:center" class="attenzioneGiallo">
							<span style="padding: 4px 0 4px 16px;">';
			$msgbox2 = '
								<br>&nbsp;
							</span>
						</div>
					</div>
				</div>
			</div>';
			$str = "";
			$this->view->message = $str;

			switch ($errors->type) {
				case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
				case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
				case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

					// 404 error -- controller or action not found
					$this->getResponse()->setHttpResponseCode(404);
					$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
					$this->view->message = $msgbox1 . '
								<h1 style="color:#000;margin:0px;padding:0px">Errore 404</h1>
								<br>La pagina o il file ricercato non esiste' . $msgbox2;
					break;
				default:
					// application error
					$this->getResponse()->setHttpResponseCode(500);
					$this->view->message = $msgbox1 . '
								<h1 style="color:#000;margin:0px;padding:0px">Application error</h1>
								<br>applicazione errata, verificare codice' . $msgbox2;

					// '/tmp/applicationException.log'
//					$log2 = new Zend_Log(
//									new Zend_Log_Writer_Stream(
//									'/tmp/applicationException.log'
//									)
//					);
//					$log2->debug($exception->getMessage() . "\n" .
//									$exception->getTraceAsString());

					break;
			}


			// Log exception, if logger available
			if ($log = $this->getLog()) {
				$log->crit($this->view->message, $errors->exception);
			}

			// conditionally display exceptions
			if ($this->getInvokeArg('displayExceptions') == true) {
				$this->view->exception = $errors->exception;
			}

			$this->view->request = $errors->request;
		}

		public function getLog() {
			$bootstrap = $this->getInvokeArg('bootstrap');
			if (!$bootstrap->hasPluginResource('Log')) {
				return false;
			}
			$log = $bootstrap->getResource('Log');
			return $log;
		}

	}
	