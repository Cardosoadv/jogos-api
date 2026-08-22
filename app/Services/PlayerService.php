<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Player;
use App\Models\PlayerModel;
use App\Repositories\PlayerRepository;
use CodeIgniter\Shield\Exceptions\ValidationException;
use CodeIgniter\Shield\Validation\ValidationRules;
use Exception;

class PlayerService extends BaseService
{
    public function __construct()
    {
        $this->repository = new PlayerRepository();
    }

    public function findAll(int $limit = 0, int $offset = 0): array
    {
        $players = $this->repository->findAll($limit, $offset);

        // withIdentities() re-indexes the collection by user id; re-sequence it
        // so the API always returns a JSON array, not an object keyed by id.
        return array_values(array_map(fn (Player $player) => $this->toArray($player), $players));
    }

    public function findById(int $id): ?array
    {
        /** @var PlayerRepository $repository */
        $repository = $this->repository;
        $player     = $repository->findById($id);

        return $player === null ? null : $this->toArray($player);
    }

    /**
     * Registra um novo jogador seguindo o fluxo padrão de registro do Shield
     * (cria o usuário, a identidade de e-mail/senha, o grupo padrão e ativa a conta).
     */
    public function register(array $data): array
    {
        $rules = (new ValidationRules())->getRegistrationRules();

        // Diferente do padrão do Shield, o apelido (username) é opcional para jogadores.
        $rules['username'] = [
            'label' => 'Auth.username',
            'rules' => ['permit_empty', 'max_length[30]', 'min_length[3]', 'is_unique[users.username]'],
        ];

        $rules += [
            'name'       => 'permit_empty|max_length[150]',
            'avatar'     => 'permit_empty|max_length[255]',
            'birth_date' => 'permit_empty|valid_date',
            'phone'      => 'permit_empty|max_length[20]',
            'bio'        => 'permit_empty|max_length[500]',
        ];

        $validation = service('validation')->setRules($rules);

        if (! $validation->run($data)) {
            return $this->error(lang('Player.registerError'), ['errors' => $validation->getErrors()]);
        }

        /** @var PlayerModel $model */
        $model = $this->repository->getModel();

        $player = $model->createNewUser(array_intersect_key($data, $rules));

        // Evita violar a chave única de "username" quando o apelido não é informado:
        // string vazia conta como valor duplicado, null não conta.
        if (empty($player->username)) {
            $player->username = null;
        }

        try {
            $model->save($player);
        } catch (ValidationException) {
            return $this->error(lang('Player.registerError'), ['errors' => $model->errors()]);
        } catch (Exception $e) {
            return $this->error(lang('Player.registerError'), ['errors' => [$e->getMessage()]]);
        }

        /** @var Player $player */
        $player = $model->findById($model->getInsertID());
        $model->addToDefaultGroup($player);
        $player->activate();

        // activate() writes straight to the DB without touching this instance;
        // reload so the response reflects the real active state.
        $player = $model->withIdentities()->find($player->id);

        return $this->success(lang('Player.registerSuccess'), ['player' => $this->toArray($player)]);
    }

    public function updateAvatar(int $id, string $path): array
    {
        return $this->update($id, ['avatar' => $path]);
    }

    private function toArray(Player $player): array
    {
        return [
            'id'          => $player->id,
            'username'    => $player->username,
            'email'       => $player->getEmail(),
            'name'        => $player->name,
            'avatar'      => $player->avatar,
            'birth_date'  => $player->birth_date !== null ? $player->birth_date->toDateString() : null,
            'phone'       => $player->phone,
            'bio'         => $player->bio,
            'active'      => (bool) $player->active,
            'last_active' => $player->last_active !== null ? (string) $player->last_active : null,
            'created_at'  => $player->created_at !== null ? (string) $player->created_at : null,
            'updated_at'  => $player->updated_at !== null ? (string) $player->updated_at : null,
        ];
    }
}
