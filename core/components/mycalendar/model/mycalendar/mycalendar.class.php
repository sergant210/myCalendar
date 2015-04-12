<?php
/**
 * The base class for myCalendar.
 *
 * @package mycalendar
 */
class myCalendar {
	/* @var modX $modx */
	public $modx;
	/* @var array $initialized Контексты */
	public $initialized = array();
	public $config = array();
	protected $fields = array('id','title','description','start','end','allDay','className','color','backgroundColor','borderColor','textColor');
	protected $properties = array('className','color','backgroundColor','borderColor','textColor');
	protected $error = '';
	protected $_allDaySlot;
	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX &$modx,array $config = array()) {
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('mycalendar.core_path',$config,$this->modx->getOption('core_path').'components/mycalendar/');
		$assetsUrl = $this->modx->getOption('mycalendar.assets_url', $config, $this->modx->getOption('assets_url').'components/mycalendar/');

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl.'css/',
			'jsUrl' => $assetsUrl.'js/',
			'imagesUrl' => $assetsUrl.'images/',
			'actionUrl' => $assetsUrl.'action.php',
			'corePath' => $corePath,
			'modelPath' => $corePath.'model/'
		),$config);
		$this->config['editable'] = ($this->config['readOnly']) ? 'false' : 'true';
		$this->config['googleCalendarApiKey'] = $this->modx->getOption('mycalendar.google_calendar_api_key', $config, '');
		if (empty($this->config['defaultColor'])) $this->config['defaultColor'] ='#0070c0';

		if (!empty($this->config['googleCalendars'])) {
			$gcals = explode(',', $this->config['googleCalendars']);
			$tmp = '[{';
			foreach ($gcals as $k=>$gc) {
				$tmp .= "googleCalendarId:'".$gc."'";
				if (!empty($this->config['googleClass'])) $tmp .= ",className:'".$this->config['googleClass']."'";
			}
			$tmp .= '}]';
			$this->config['googleCalendars'] = $tmp;
		} else {
			$this->config['googleCalendars'] = '[]';
		}
		if (empty($this->config['defaultDuration'])) {
			$this->config['defaultDuration'] = "'00:30'";
		} else {
			$this->config['defaultDuration'] = $this->modx->quote($this->config['defaultDuration']);
		}
		$this->_allDaySlot = ($this->config['allDaySlot'] == 'false') ? false : true;
		$this->modx->addPackage('mycalendar',$this->config['modelPath']);
		$this->modx->lexicon->load('mycalendar:default');
	}
	/**
	 * Initializes component.
	 * @return boolean
	 */
	public function initialize() {
		if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
			$buttons = '{add:"'.$this->modx->lexicon('mc.add').'",save:"'.$this->modx->lexicon('mc.save').'",delete:"'.$this->modx->lexicon('mc.delete').'",close:"'.$this->modx->lexicon('mc.close').'"}';
			$str = '{newEvent:"'.$this->modx->lexicon('mc.newEvent').'",editEvent:"'.$this->modx->lexicon('mc.editEvent').'",colorString:"'.$this->modx->lexicon('mc.colorString').'",deleteEvent:"'.$this->modx->lexicon('mc.deleteEvent').'"}';
			$this->modx->regClientCSS($this->config['cssUrl'].'bootstrap.min.css');
			$this->modx->regClientCSS($this->config['cssUrl'].'fullcalendar.min.css');
			$this->modx->regClientCSS($this->config['cssUrl'].'jquery-ui.min.css');
			$this->modx->regClientCSS($this->config['cssUrl'].'jquery.timepicker.min.css');
			$this->modx->regClientCSS($this->config['cssUrl'].'jquery.qtip.min.css');
			$this->modx->regClientCSS($this->config['cssUrl'].'evol.colorpicker.min.css');
			$this->modx->regClientCSS($this->config['cssUrl'].'default.min.css');
			$config_js = preg_replace(array('/^\n/', '/\t{4}/'), '', '
				var mcal_config = {
					actionUrl: "'.$this->config['actionUrl'].'"
					,calendarName: "'.$this->modx->lexicon('mc.calendarName').'"
					,buttons: '.$buttons.'
					,str: '.$str.'
					,defaultDuration: '.$this->config['defaultDuration'].'
					,showWeekends: '.$this->config['showWeekends'].'
					,showWeekNumber: '.$this->config['showWeekNumber'].'
					,editable: '.$this->config['editable'].'
					,height: '.$this->config['height'].'
					,axisFormat: '.$this->config['axisFormat'].'
					,allDaySlot: '.$this->config['allDaySlot'].'
					,minTime: '.$this->config['minTime'].'
					,maxTime: '.$this->config['maxTime'].'
					,defaultView: '.$this->config['defaultView'].'
					,fixedWeekCount: '.$this->config['fixedWeekCount'].'
					,googleCalendarApiKey: "'.$this->config['googleCalendarApiKey'].'"'.'
					,googleCalendars: '.$this->config['googleCalendars']
			);
			/*
			if (!empty($this->config['googleCalendarApiKey'])) {
				$config_js .= "\n\t,googleCalendarApiKey: '".$this->config['googleCalendarApiKey']."'";
				$config_js .= "\n\t,googleCalendars: ".$gcals;
			}
			*/
			if (!empty($this->config['hiddenDays']))
				$config_js .= "\n\t,hiddenDays: [".$this->config['hiddenDays'].']';
			$config_js .= "\n};";
			$this->modx->regClientStartupScript("<script type=\"text/javascript\">\n".$config_js."\n</script>", true);
			$this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{5}/'), '', '
					<script type="text/javascript">
						if(typeof jQuery == "undefined") {
							document.write("<script src=\"'.$this->config["jsUrl"].'lib/jquery-2.0.3.min.js\" type=\"text/javascript\"><\/script>");
						}
					</script>
					'), true);
			$this->modx->regClientScript($this->config['jsUrl'].'lib/moment.min.js');
			$this->modx->regClientScript($this->config['jsUrl'].'lib/jquery-ui.min.js');
			$this->modx->regClientScript($this->config['jsUrl'].'fullcalendar.min.js');
			$this->modx->regClientScript($this->config['jsUrl'].'lang/ru.js');
			$this->modx->regClientScript($this->config['jsUrl'].'gcal.js');
			$this->modx->regClientScript($this->config['jsUrl'].'lib/jquery.timepicker.min.js');
			$this->modx->regClientScript($this->config['jsUrl'].'lib/jquery.qtip.min.js');
			$this->modx->regClientScript($this->config['jsUrl'].'lib/evol.colorpicker.min.js');
			$this->modx->regClientScript($this->config['jsUrl'].'default.js');
		}
	}
	/** Получает все события.
	 * @param array $data POST данные
	 * @return array
	 */
	public function getEvents ($data = array()) {
		$allEvents = array();
		$query = $this->modx->newQuery('myCalendarEvents');
		$query->setClassAlias('Events');
		$select =  $this->modx->getSelectColumns('myCalendarEvents','Events');
		$query->select($select);
		$data['start'] = date('Y-m-d H:i',strtotime($data['start']));
		$data['end'] = date('Y-m-d H:i',strtotime($data['end']));
		//Ограничение по периоду
		$where = 'Events.start BETWEEN '.$this->modx->quote($data['start']).' AND '.$this->modx->quote($data['end']);
		$query->where($where);

		if ($query->prepare() && $query->stmt->execute()) {
			$allEvents = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		$calendarEvents = array();
		foreach ($allEvents as $event) {
			if (!empty($event['properties'])) {
				$event['properties'] = $this->modx->fromJSON($event['properties']);
			} else {
				$event['properties'] = array();
			}
			$tmp = array();
			foreach ($this->fields as $field) {
				if (in_array($field,$this->properties)) {
					if (isset($event['properties'][$field])) $tmp[$field] = $event['properties'][$field];
				} elseif ($field == 'allDay') {
					$tmp[$field] = (bool) $event[$field];
				} else {
					$tmp[$field] = $event[$field];
				};
			}
			$tmp['google'] = false;
			if (empty($tmp['color']) && !empty($this->config['defaultColor'])) {
				$tmp['color'] = $this->config['defaultColor'];
			}
			$tmp['className'] = (!empty($tmp['className'])) ? $tmp['className'].' ' . $this->getTheme($tmp['color']) : $this->getTheme($tmp['color']);
			$calendarEvents[] = $tmp;
		}
		return $calendarEvents;
	}

	/** Диалог редактирования
	 * @param array $data
	 * @return string
	 */
	public function openDialog($data = array()) {
		if (empty($data)) return "";
		$id = (int) $data['id'];
		$res = $event = array();
		if ($id != 0) {
			$query = $this->modx->newQuery('myCalendarEvents');
			$query->setClassAlias('Events');
			$select =  $this->modx->getSelectColumns('myCalendarEvents','Events');
			$query->select($select);
			$query->where(array('Events.id'=>$id));
			if ($query->prepare() && $query->stmt->execute()) {
				$res = $query->stmt->fetch(PDO::FETCH_ASSOC);
			}
			if (!empty($res)) {
				$res['properties'] = $this->modx->fromJSON($res['properties']);
				foreach ($this->fields as $field) {
					if (in_array($field, $this->properties)) {
						if (isset($res['properties'][$field]))
							$event[$field] = $res['properties'][$field];
					}
					elseif ($field == 'start' || $field == 'end') {
						$event[$field.'_date'] = (!empty($res[$field])) ? date('d.m.Y',strtotime($res[$field])) : '';
						$event[$field.'_time'] = (!empty($res[$field])) ? date('H:i',strtotime($res[$field])) : '';
					}
					else {
						$event[$field] = $res[$field];
					};
				}
			}
		} else {
			$event['start_date'] = date('d.m.Y',strtotime($data['start']));
			$event['start_time'] = date('H:i',strtotime($data['start']));
			$event['end_date'] = date('d.m.Y',strtotime($data['end']));
			$event['end_time'] = date('H:i',strtotime($data['end']));
			$event['allDay'] = ($data['allDay']=='true') ? true : false;
		}
		$dlg = $this->modx->getChunk($this->config['tpl'],$event);
		if (strpos($dlg,'[[+')!= false) $dlg = preg_replace('/\[\[.+?\]\]/','',$dlg);
		return $dlg;
	}
	/** Сохраняет событие
	 * @param array $data POST данные
	 * @return array
	 */
	public function saveEvent ($data = array()) {
		if ($this->config['readOnly']) return $this->error('ReadOnly mode!');
		$data = array_map('trim',$data);
		$event = $output = array();
		switch ($data['mode']) {
			case 'new':
			case 'edit':
				// Проверяем обязательные поля title, calendar, start_date
				$res = $this->validateData($data);
				if (!$res['success']) return $res;
				$start = $end = array();
				//todo Сделать через фукнцию
				// $event = $this->prepareData($data);
				$event['title'] = $this->modx->sanitizeString($data['title']);
				$event['description'] = $this->modx->sanitizeString($data['description']);
				$event['allDay'] = ($data['allDay'] == 'false') ? false : true;
				// start_date обязательно
				$start['date'] =  date('Y-m-d',strtotime($data['start_date']));
				if ($event['allDay'] || empty($data['start_time']) ) {
					$start['time'] = '00:00';
				} else {
					$start['time'] = date('H:i',strtotime($data['start_time']));
				}
				$start['hasTime'] = $start['time'] == '00:00' ? false : true;
				if (!empty($data['end_date'])) {
					$end['date'] =  date('Y-m-d',strtotime($data['end_date']));
					if ($event['allDay'] || empty($data['end_time']) ) {
						$end['time'] = '00:00';
						$end['hasTime'] = false;
					} else {
						$end['time'] = date('H:i',strtotime($data['end_time']));
						$end['hasTime'] = true;
					}
				} else {
					// формируем конечную дату согласно настроек
					$end = $this->getEndDateTime($start,$event['allDay']);
					if ($end['time'] == '00:00') {
						$end['hasTime'] = false;
					} else {
						$end['hasTime'] = true;
					}
				}
				$end['hasTime'] = $end['time'] == '00:00' ? false : true;

				if (!$event['allDay'] && !$start['hasTime'] && !$end['hasTime'] && !$this->_allDaySlot) {
					$event['allDay'] = true;
				}
				//Проверка на правильность конечной даты - не больше начальной
				if (!$this->isCorrectPeriod($start,$end,$event['allDay'])) {
					$end = $this->getEndDateTime($start,$event['allDay']);
				}

				$event['start'] = implode(' ', array_slice($start,0,2));
				$event['end'] = implode(' ', array_slice($end,0,2));
				$event_arr = array();
				foreach ($this->properties as $field) {
					if (!empty($data[$field])) {
						$event_arr['properties'][$field] = $this->modx->stripTags($data[$field]);
					}
				}
				if (!empty($event_arr['properties'])) $event['properties'] = $this->modx->toJSON($event_arr['properties']);
				// если пользователь не авторизован, то событие не может быть личным
				/** @var myCalendarEvents $e  */
				if ($data['mode'] == 'new') {
					$e = $this->modx->newObject('myCalendarEvents');
				} else {
					$e = $this->modx->getObject('myCalendarEvents',intval($data['id']));
				}
				$e->fromArray($event);
				if (!$e->save()) return $this->error($this->modx->lexicon('mc.save_error'));

				/** формируем данные для календаря  */
				$output = $this->getEvent($e);
				break;
			case 'move':
				/** @var myCalendarEvents $e  */
				//print('<pre>');print_r($data);die();
				$e = $this->modx->getObject('myCalendarEvents',intval($data['id']));
				$start = date('Y-m-d H:i',strtotime($data['start']));
				if (isset($data['end'])) {
					$end = date('Y-m-d H:i', strtotime($data['end']));
				} else {
					$end = null;
				}
				$data['allDay'] = ($data['allDay'] == 'false') ? 0 : 1;
				$e->set('start',$start);
				$e->set('end',$end);
				$e->set('allDay',$data['allDay']);
				if (!$e->save()) return $this->error($this->modx->lexicon('mc.save_error'));
				break;
			case 'resize':
				/** @var myCalendarEvents $e  */
				$e = $this->modx->getObject('myCalendarEvents',intval($data['id']));
				$start = date('Y-m-d H:i',strtotime($data['start']));
				$end = date('Y-m-d H:i',strtotime($data['end']));
				$e->set('start',$start);
				$e->set('end',$end);
				if (!$e->save()) return $this->error($this->modx->lexicon('mc.save_error'));
				break;
		}
		return $this->success('',$output);
	}
	/**
	 * @param myCalendarEvents $event
	 * @return array
	 */
	public function getEvent (myCalendarEvents $event) {
		$event_arr = $event->toArray();
		$properties = array();
		if (!empty($event_arr['properties'])) $properties = $this->modx->fromJSON($event_arr['properties']);
		$output = '';
		foreach ($this->fields as $field) {
			if ($field == 'id') {
				$output['id'] = $event->get('id');
			} elseif (in_array($field,$this->properties)) {
				if (isset($properties[$field])) $output[$field] = $properties[$field];
			} elseif ($field == 'allDay') {
				$output[$field] = (bool) $event_arr[$field];
			} else {
				$output[$field] = $event_arr[$field];
			};
		}
		$output['google'] = false;
		if (empty($output['color']) && !empty($this->config['defaultColor'])) {
			$output['color'] = $this->config['defaultColor'];
		}
		$output['className'] = (!empty($output['className'])) ? $output['className'].' ' . $this->getTheme($output['color']) : $this->getTheme($output['color']);
		return $output;
	}
	/** Проверяет конечную дату. Конечная дата должна быть больше начальной минимум на 30 мин.
	 * @param array $start
	 * @param array $end
	 * @param bool $allDay
	 * @return bool
	 */
	protected function isCorrectPeriod ($start,$end,$allDay=false) {
		$start_date = strtotime(implode(' ', array_slice($start,0,2)));
		$end_date = strtotime(implode(' ', array_slice($end,0,2)));
		if ($allDay) {
			return $end_date - $start_date >= 86400;
		} else {
			return $end_date - $start_date >= 1800;
		}
	}
	/** Формирует конечную дату события или конечную дату периода
	 * @param array $start
	 * @param bool $allDay
	 * @return array
	 */
	protected function getEndDateTime($start,$allDay=false) {
		$start_date = implode(' ', array_slice($start,0,2));
		$date = date_create($start_date);
		if ($allDay) {
			$duration = 86400;
		} else {
			$duration = strtotime(trim($this->config['defaultDuration'], "'")) - strtotime('00:00');
		}
		$di = new DateInterval('PT'.$duration.'S');
		$end_date = date_add($date, $di);
		$end['date'] = date_format($end_date, 'Y-m-d');
		$end['time'] = date_format($end_date, 'H:i');
		return $end;
	}
	/** Формирует конечную дату периода
	 * @param string $start_date В формате даты 'Y-m-d H:i'
	 * @param integer $duration Интервал в секундах
	 * @return string
	 */
	protected function getEndPeriod($start_date, $duration = 0) {
		$date = date_create($start_date);
		$di = new DateInterval('PT'.$duration.'S');
		$end = date_add($date, $di);
		return date_format($end, 'Y-m-d H:i');
	}

	/** Удаляет событие
	 * @param integer $id ID удаляемого события
	 * @return bool
	 */
	public function removeEvent ($id = 0) {
		if (empty($id)) return $this->error($this->modx->lexicon['mc.no_id_event']);
		/** @var myCalendarEvents $event */
		$event = $this->modx->getObject('myCalendarEvents',$id);
		if (is_object($event)) $event->remove();
		return $this->success();
	}
	/** Проверяет корректность заполнения данных события перед сохранением
	 * @param $data
	 * @return string
	 * @internal array $date
	 */
	public function validateData ($data) {
		$required_fields = array('title'=>$this->modx->lexicon('mc.title'),'start_date'=>$this->modx->lexicon('mc.start_date'));
		if (!empty($data['start_time'])) {
			if (preg_match('/^\d{1,2}:\d{2}$/',$data['start_time'],$match)) {
				if (intval($match[1]) > 23 || intval($match[2]) > 59)
					return array('success' => FALSE, 'message' => $this->modx->lexicon('mc.incorrect_time_format'), 'field' => 'start_time');
			} else {
				return array('success' => FALSE, 'message' => $this->modx->lexicon('mc.incorrect_time_format'), 'field' => 'start_time');
			}
		}
		if (!empty($data['end_time'])) {
			if (preg_match('/^\d{1,2}:\d{2}$/',$data['end_time'],$match)) {
				if (intval($match[1]) > 23 || intval($match[2]) > 59)
					return array('success' => FALSE, 'message' => $this->modx->lexicon('mc.incorrect_time_format'), 'field' => 'end_time');
			} else {
				return array('success' => FALSE, 'message' => $this->modx->lexicon('mc.incorrect_time_format'), 'field' => 'end_time');
			}
		}

		foreach ($required_fields as $field => $name) {
			if (empty($data[$field])) {
				return array('success' => FALSE, 'message' => $this->modx->lexicon('mc.error_field', array('name'=>$name)), 'field' => $field);
			}
		}
		return array('success' => TRUE);
	}

	/** Определяет тему
	 * @param string $color
	 * @return string
	 */
	protected function getTheme($color = '') {
		switch ($color) {
			case '#ffffff': case '#dbe5f1': case '#f2dcdb': case '#ebf1dd': case '#e5e0ec': case '#dbeef3': case '#fdeada':
			case '#eeece1': case '#c4bd97': case '#8db3e2': case '#b8cce4': case '#f2f2f2': case '#e5b9b7': case '#ccc1d9':
			case '#fbd5b5': case '#bfbfbf': case '#938953': case '#95b3d7': case '#d99694': case '#c3d69b': case '#b2a2c7':
			case '#ddd9c3': case '#c6d9f0': case '#00b0f0': case '#ffc000': case '#ffff00': case '#9bbb59': case '#4bacc6':
			case '#92d050': case '#a5a5a5': case '#7f7f7f': case '#fac08f': case '#f79646': case '#b7dde8': case '#d8d8d8':
			$theme='light-theme';
			break;
			default:
				$theme='dark-theme';
				break;
		}
		return $theme;
	}
	/** This method returns an error
	 *
	 * @param string $message Error message
	 * @param array $data. Field
	 *
	 * @return array $response
	 */
	protected function error($message = '', $data = array()) {
		$response = array(
			'success' => FALSE,
			'message' => $message,
			'field' => $data
		);

		return $response;
	}

	/** This method returns a success
	 *
	 * @param string $message Success message
	 *
	 * @return array $response
	 * */
	protected function success($message = '',$data = array()) {
		$response = array(
			'success' => TRUE,
			'message' => $message,
			'data' => $data
		);

		return $response;
	}
}