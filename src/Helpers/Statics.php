<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Helpers;

class Statics
{

    public const CHAT_GPT_MODEL = 'gpt-4o';

    public const URL_CREATE_ASSISTANT = 'https://api.openai.com/v1/assistants';

    public const URL_DELETE_ASSISTANT = 'https://api.openai.com/v1/assistants/%s';

    public const URL_MODIFY_ASSISTANT = 'https://api.openai.com/v1/assistants/%s';

    public const URL_CREATE_FILE_UPLOAD = 'https://api.openai.com/v1/files';

    public const URL_DELETE_FILE_UPLOAD = 'https://api.openai.com/v1/files/%s';

    public const URL_CREATE_VECTOR_STORES = 'https://api.openai.com/v1/vector_stores';

    public const URL_RETRIEVE_VECTOR_STORES = 'https://api.openai.com/v1/vector_stores/%s';

    public const URL_DELETE_VECTOR_STORES = "https://api.openai.com/v1/vector_stores/%s";

    public const URL_CREATE_THREAD = 'https://api.openai.com/v1/threads';

    public const URL_CREATE_THREAD_MESSAGE = "https://api.openai.com/v1/threads/%s/messages";

    public const URL_RUN_THREAD = "https://api.openai.com/v1/threads/%s/runs";

    public const URL_RETRIEVE_RUN = "https://api.openai.com/v1/threads/%s/runs/%s";
}