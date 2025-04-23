<?php

namespace xamned\framework\http\resource;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\form\FormRequestInterface;
use xamned\framework\contracts\http\FormRequestFactoryInterface;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;
use xamned\framework\contracts\http\resource\ResourceWriterInterface;
use xamned\framework\contracts\resource\RelationshipsManagerInterface;
use xamned\framework\form\FormRequest;
use xamned\framework\http\exceptions\ForbiddenHttpException;
use xamned\framework\http\exceptions\HttpBadRequestException;
use xamned\framework\http\exceptions\HttpNotFoundException;
use xamned\framework\http\resource\responses\CreateResponse;
use xamned\framework\http\resource\responses\DeleteResponse;
use xamned\framework\http\resource\responses\JsonResponse;
use xamned\framework\http\resource\responses\PatchResponse;
use xamned\framework\http\resource\responses\UpdateResponse;

abstract class AbstractResourceController
{
    public function __construct(
        protected ResourceDataFilterInterface $resourceDataFilter,
        protected ServerRequestInterface $request,
        protected FormRequestFactoryInterface $formRequestFactory,
        protected ResourceWriterInterface $resourceWriter,
        protected RelationshipsManagerInterface $relationshipsManager,
        protected ContainerInterface $container,
    ) {
        $this->resourceDataFilter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFields())
            ->setAccessibleFilters($this->getAccessibleFilters())
            ->setExpands($this->getExpands());

        $this->resourceWriter
            ->setResourceName($this->getResourceName());

        $this->relationshipsManager
            ->setResourceName($this->getResourceName())
            ->setRelationships($this->getRelationships());
    }

    protected $forms = [
        ResourceActionTypesEnum::CREATE->value => FormRequest::class,
        ResourceActionTypesEnum::UPDATE->value => FormRequest::class,
        ResourceActionTypesEnum::PATCH->value => FormRequest::class,
    ];

    protected function getAvailableActions(): array
    {
        return [
            ResourceActionTypesEnum::INDEX,
            ResourceActionTypesEnum::VIEW,
            ResourceActionTypesEnum::CREATE,
            ResourceActionTypesEnum::UPDATE,
            ResourceActionTypesEnum::PATCH,
            ResourceActionTypesEnum::DELETE,
        ];
    }

    abstract protected function getResourceName(): string;

    /**
     * Возврат имен свойств ресурса, доступных к чтению
     * Пример запроса:
     * ?fields=id,order_id,name
     * @return array
     */
    abstract protected function getAccessibleFields(): array;
    
    /**
     * Возврат имен свойств ресурса, доступных к фильтрации
     * Пример запроса:
     * ?filter[order_id][$eq]=3
     * @return array
     */
    abstract protected function getAccessibleFilters(): array;

    /**
     * Возврат имен ресурсов, доступных к расширению
     * Пример запроса:
     * ?expand=months
     *
     * Пример записи:
     * [
     *      'expandResourceName' => 'expandResourceName.expandFieldName = resourceName.fieldName'
     *      ...
     * ]
     *
     * @return array
     */
    protected function getExpands(): array
    {
        return [];
    }

    protected function getFormRules(ResourceActionTypesEnum $actionType): array
    {
        return [];
    }

    protected function getFormName(ResourceActionTypesEnum $actionType): ?string
    {
        return '';
    }

    protected function createForm(ResourceActionTypesEnum $actionType): FormRequestInterface
    {
        return $this->formRequestFactory->create(
            $this->forms[$actionType->value],
            $this->getFormName($actionType),
            $this->getFormRules($actionType)
        );
    }

    protected function setResponseStatus(int $code, string $reasonPhrase = ''): void
    {
        /** @var ResponseInterface */
        $response = $this->container->get(ResponseInterface::class);
        $response = $response->withStatus($code, $reasonPhrase);

        $this->container->attach(ResponseInterface::class, function() use ($response) {
            return $response;
        });
    }

    protected function getRelationships(): array
    {
        return [];
    }

    /**
     * @throws ForbiddenHttpException
     */
    private function checkCallAvailability(ResourceActionTypesEnum $actionType): void
    {
        if (in_array($actionType, $this->getAvailableActions()) === false) {
            throw new ForbiddenHttpException();
        }
    }
    
    /**
     * Возврат ресурсов, по ограничениям указанным в строке запроса
     * 
     * Пример запроса:
     * ?fields[]=id&fields[]=order_id&fields[]=name&filter[order_id][$eq]=3
     * Пример ответа:
     * application/json
     * [
     *     {
     *         "id": 1,
     *         "order_id":3,
     *         "name": "Некоторое имя 1"
     *     },
     *     {
     *         "id": 2,
     *         "order_id":3,
     *         "name": "Некоторое имя 2"
     *     },
     *     ...
     * ]
     * @return JsonResponse
     */
    public function actionList(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::INDEX);
        
        $data = $this->resourceDataFilter->filterAll($this->request->getQueryParams());

        return new JsonResponse($data);
    }

    /**
     * Возврат ресурса, по ограничениям указанным в строке запроса
     * 
     * Пример запроса:
     * ?fields[]=id&fields[]=name&filter[id][$eq]=1
     * Пример ответа:
     * application/json
     * {
     *     "id": 1,
     *     "name": "Некоторое имя 1"
     * },
     * @return array
     */
    public function actionView(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::VIEW);

        $data = $this->resourceDataFilter->filterOne($this->request->getQueryParams());

        if ($data === null) {
            throw new HttpNotFoundException('Запрашиваемый ресурс не найден');
        }

        return new JsonResponse($data);
    }

    public function actionCreate(): CreateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::CREATE);

        $form = $this->createForm(ResourceActionTypesEnum::CREATE);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->create($form->getValues());

        $resourceItem = $this->resourceDataFilter->filterOne(['filter' => $form->getValues()]);

        $this->relationshipsManager->manage($this->request, $resourceItem);

        return new CreateResponse();
    }

    public function actionUpdate(string|int $id): UpdateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::UPDATE);

        $form = $this->createForm(ResourceActionTypesEnum::UPDATE);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $resourceItem = $this->resourceDataFilter->filterOne(['filter' => ['id' => $id]]);

        if ($resourceItem === null) {
            throw new HttpNotFoundException('Запрашиваемый ресурс не найден');
        }

        $this->resourceWriter->update($id, $form->getValues());

        $this->relationshipsManager->manage($this->request, $resourceItem);

        return new UpdateResponse();
    }

    public function actionPatch(string|int $id): PatchResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::PATCH);
        
        $form = $this->createForm(ResourceActionTypesEnum::PATCH);

        $form->setSkipEmptyValues();

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $resourceItem = $this->resourceDataFilter->filterOne(['filter' => ['id' => $id]]);

        if ($resourceItem === null) {
            throw new HttpNotFoundException('Запрашиваемый ресурс не найден');
        }

        $this->resourceWriter->patch($id, $form->getValues());

        $this->relationshipsManager->manage($this->request, $resourceItem);

        return new PatchResponse();
    }

    public function actionDelete(string|int $id): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::DELETE);

        $resourceItem = $this->resourceDataFilter->filterOne(['filter' => ['id' => $id]]);

        if ($resourceItem === null) {
            throw new HttpNotFoundException('Запрашиваемый ресурс не найден');
        }

        $this->relationshipsManager->manage($this->request, $resourceItem);

        $this->resourceWriter->delete($id);

        return new DeleteResponse();
    }
}
