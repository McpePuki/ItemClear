<?php
/**
 * @name ItemClear
 * @main ItemClear\ItemClear
 * @author Puki
 * @version 1.0.0
 * @api 4.0.0
 */
namespace ItemClear;

    use pocketmine\event\Listener;
    use pocketmine\Player;
    use pocketmine\command\PluginCommand;
    use pocketmine\Server;
    use pocketmine\command\CommandSender;
    use pocketmine\scheduler\Task;
    use pocketmine\command\Command;
    use pocketmine\utils\Config;
    use pocketmine\plugin\PluginBase;
    use pocketmine\entity\Entity;
    use pocketmine\level\level;
    use pocketmine\entity\object\ItemEntity;
    use pocketmine\entity\projectile\Arrow;
    use pocketmine\event\entity\EntitySpawnEvent;

    class ItemClear extends PluginBase implements Listener {

        public function onEnable() {
            $this->getServer()->getPluginManager()->registerEvents($this, $this);

            $this->data = new Config ($this->getDataFolder() . 'clearTime', Config::YAML,[
              '청소시간' => '30',
            ]);
            $this->db = $this->data->getAll();

            $cmd = new PluginCommand('청소시간', $this);
            $cmd->setDescription('청소시간 설정 명령어');

            $this->getServer()->getCommandMap()->register('청소시간', $cmd);

            $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            public function __construct(ItemClear $owner) {
              $this->owner = $owner;
            }
            public function onRun(int $currentTick) {
                foreach ($this->owner->getServer()->getLevels() as $level){
                  foreach ($level->getEntities() as $entity){
                  if(isset($this->owner->itemdata[$entity->getId()])){
                    if($entity instanceof ItemEntity or $entity instanceof Arrow){
                    if($entity == null){
                      unset($this->owner->itemdata[$entity->getId()]);
                      return true;
                    }
                    $this->owner->itemdata[$entity->getId()]--;
                    if($this->owner->itemdata[$entity->getId()] <= 0 ){
                      $entity->close();
                  }
                }
              }
            }
          }
        }
        }, 20 );
        }

        public function save(){
          $this->data->setAll ( $this->db );
          $this->data->save ();
        }

        public $itemdata = [];
        function onDropItem(EntitySpawnEvent $ev){
          $entity = $ev->getEntity();
          if($entity instanceof ItemEntity or $entity instanceof Arrow){
            $this->itemdata[$entity->getId()] = $this->db['청소시간'];
        }
      }

      function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        $cmd = $command->getName();
        if($cmd == '청소시간'){
          if(!isset($args[0])){
            $sender->sendMessage('/청소시간 [정수(int)초]');
            return true;
          }
          if(!is_numeric($args[0])){
            $sender->sendMessage('청소시간은 숫자로만 가능합니다.');
            return true;
          }
          if($args[0] <= 0){
            $sender->sendMessage('0보다는 커야합니다.');
            return true;
          }
          if(isset($args[0])){
            $this->db['청소시간'] = $args[0];
            return true;
          }
          return false;
        }
      }
    }
