<?php

namespace xamned\framework\http\resource;

use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\http\FormRequestFactoryInterface;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;
use xamned\framework\contracts\http\resource\ResourceWriterInterface;
use xamned\framework\http\exceptions\ForbiddenHttpException;
use xamned\framework\http\exceptions\HttpBadRequestException;
use xamned\framework\http\FormRequest;
use xamned\framework\http\resource\ResourceActionTypesEnum;
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
        protected ContainerInterface $container,
    ) {
        $this->resourceDataFilter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFields())
            ->setAccessibleFilters($this->getAccessibleFilters());

        $this->resourceWriter
            ->setResourceName($this->getResourceName());
    }

    private $forms = [
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

        /** @var JsonResponse */
        $response = $this->container->get(JsonResponse::class);

        $data = $this->resourceDataFilter->filterAll($this->request->getQueryParams());

        $response->getBody()->write(json_encode($data));
        
        return $response;
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

        /** @var JsonResponse */
        $response = $this->container->get(JsonResponse::class);

        $data = $this->resourceDataFilter->filterOne($this->request->getQueryParams());

        $response->getBody()->write(json_encode($data));
        
        return $response;
    }

    public function actionCreate(): CreateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::CREATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::CREATE->value]);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->create($form->getValues());

        return $this->container->get(CreateResponse::class);
    }

    public function actionUpdate(string|int $id): UpdateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::UPDATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::UPDATE->value]);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->update($id, $form->getValues());

        return $this->container->get(UpdateResponse::class);
    }

    public function actionPatch(string|int $id): PatchResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::PATCH);
        
        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::PATCH->value]);

        $form->setSkipEmptyValues();

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->patch($id, $form->getValues());

        return $this->container->get(PatchResponse::class);
    }

    public function actionDelete(string|int $id): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::DELETE);
        
        $this->resourceWriter->delete($id);
        
        return $this->container->get(DeleteResponse::class);
    }
}
