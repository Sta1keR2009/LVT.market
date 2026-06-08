<?php

namespace Ipol\Catapulto\Bitrix\Entity;

use Ipol\Catapulto\Catapulto\Entity\AbstractResult;

class ErrorCollector extends BasicResponse
{
    /**
     * @param AbstractResult|null $result
     */
    public function __construct(?AbstractResult $result = null)
    {
        $this->setSuccess(false);
        $this->collect($result);
    }

    /**
     * @var AbstractResult $result
     * @return BasicResponse
     */
    private function collect(?AbstractResult $result)
    {
        $arReturnErrors = [];
        $errorObj = false;

        if ($result instanceof AbstractResult) {
            /** @var AbstractResult $result */

            if ($result->getResponse()) {
                $errorObj = $result->getResponse();
            } elseif ($result->getError()) {
                $errorObj = $result->getError();
            }

            if (is_object($errorObj)) {
                if (is_array($errorObj->getMessage())) {
                    foreach ($errorObj->getMessage() as $field => $error) {
                        if (is_array($error)) {
                            $arReturnErrors[] = $field . ': ' . implode(', ', $error);
                        } else {
                            $arReturnErrors[] = $field . ': ' . $error;
                        }
                    }
                } else {
                    $arReturnErrors[] = $errorObj->getMessage();
                }
            }

        }

        $this->setSuccess(false)
            ->setErrorText(implode(', ',$arReturnErrors));

        return $this;
    }


}