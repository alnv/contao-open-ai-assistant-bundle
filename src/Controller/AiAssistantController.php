<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Controller;

use Alnv\ContaoOpenAiAssistantBundle\Components\AiSearchComponent;
use Alnv\ContaoOpenAiAssistantBundle\Helpers\Toolkit;
use Contao\CoreBundle\Controller\AbstractController;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Alnv\ContaoOpenAiAssistantBundle\Chat\Agent;
use Symfony\Component\Routing\Annotation\Route;
use Contao\System;
use Contao\Input;

#[Route(path: 'open-ai-assistant', name: 'open-ai-assistant-controller', defaults: ['_scope' => 'frontend', '_token_check' => false])]
class AiAssistantController extends AbstractController
{

    #[Route(path: '/search/{assistantName}', methods: ["POST"])]
    public function searchWithAssistant($assistantName): JsonResponse
    {

        $this->container->get('contao.framework')->initialize();

        $strSessionKeyId = 'open-ai-' . md5($assistantName) . '-thread-id';
        $strPrompt = Input::get('prompt') ?: (Input::post('prompt') ?: '');
        $arrAiSearchOptions = StringUtil::deserialize(Toolkit::getSerializedArrayFromBase64(Input::post('options') ?: ''), true);
        $objAiSearchComponent = new AiSearchComponent($arrAiSearchOptions);

        $objSession = System::getContainer()->get('request_stack')->getSession();
        $strThreadId = $objSession->get($strSessionKeyId);

        if ($strThreadId) {
            $arrAiSearchOptions['thread_id'] = $strThreadId;
        }

        $objAgent = new Agent($assistantName, $arrAiSearchOptions);
        $objSession->set($strSessionKeyId, $objAgent->getThreadId());

        if ($strPrompt) {
            $objAgent->addMessage($strPrompt);
        }

        $arrAgentArray = $objAgent->getCurrentAgentArray();

        return new JsonResponse([
            'lastPrompt' => $arrAgentArray['last_prompt'] ?? '',
            'messages' => $objAiSearchComponent->parseMessages($objAgent->getMessages())
        ]);
    }

    #[Route(path: '/delete/thread/{assistantName}', methods: ["POST"])]
    public function deleteThread($assistantName): JsonResponse
    {

        $this->container->get('contao.framework')->initialize();

        $objSession = System::getContainer()->get('request_stack')->getSession();
        $strSessionKeyId = 'open-ai-' . md5($assistantName) . '-thread-id';
        $objSession->set($strSessionKeyId, '');

        return new JsonResponse([]);
    }
}