<?php
class Calendar {
    private $active_year;
    private $active_month;
    private $active_day;
    private $events = [];
    private $timezone;
    private $highlight_today = true;
    private $show_other_months = true;
    private $first_day_of_week = 0; // 0=Sunday, 1=Monday

    public function __construct($date = null, $timezone = 'UTC') {
        try {
            $this->timezone = new DateTimeZone($timezone);
            $current_date = new DateTime('now', $this->timezone);
            
            if ($date !== null) {
                $parsed_date = new DateTime($date, $this->timezone);
                $this->active_year = $parsed_date->format('Y');
                $this->active_month = $parsed_date->format('m');
                $this->active_day = $parsed_date->format('d');
            } else {
                $this->active_year = $current_date->format('Y');
                $this->active_month = $current_date->format('m');
                $this->active_day = $current_date->format('d');
            }
        } catch (Exception $e) {
            // Fallback to current date if invalid date/timezone provided
            $this->active_year = date('Y');
            $this->active_month = date('m');
            $this->active_day = date('d');
            $this->timezone = new DateTimeZone('UTC');
        }
    }

    public function add_event($text, $date, $days = 1, $color = '', $data_attributes = []) {
        try {
            $event_date = new DateTime($date, $this->timezone);
            $this->events[] = [
                'text' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                'date' => $event_date->format('Y-m-d'),
                'days' => max(1, (int)$days),
                'color' => $this->validate_color($color),
                'data' => $data_attributes
            ];
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function set_first_day_of_week($day) {
        $this->first_day_of_week = in_array($day, [0, 1]) ? $day : 0;
    }

    public function show_other_months($show) {
        $this->show_other_months = (bool)$show;
    }

    public function highlight_today($highlight) {
        $this->highlight_today = (bool)$highlight;
    }

    private function validate_color($color) {
        if (empty($color)) return '';
        return ' ' . preg_replace('/[^a-zA-Z0-9-_]/', '', $color);
    }

    public function render($options = []) {
        try {
            $options = array_merge([
                'header' => true,
                'navigation' => true,
                'footer' => false,
                'month_year_format' => 'F Y'
            ], $options);

            $current_date = new DateTime("{$this->active_year}-{$this->active_month}-01", $this->timezone);
            $month_name = $current_date->format($options['month_year_format']);
            $num_days = $current_date->format('t');
            
            // Adjust first day of week based on setting
            $first_day_of_week = (int)$current_date->format('w'); // 0-6 (Sun-Sat)
            if ($this->first_day_of_week === 1 && $first_day_of_week === 0) {
                $first_day_of_week = 6; // Monday-first, Sunday becomes last
            } elseif ($this->first_day_of_week === 1) {
                $first_day_of_week--; // Adjust for Monday-first
            }

            // Previous month's days needed
            $prev_month = (clone $current_date)->modify('first day of previous month');
            $num_days_last_month = $prev_month->format('t');
            
            // Next month
            $next_month = (clone $current_date)->modify('first day of next month');
            
            // Days of week labels
            $days_of_week = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            if ($this->first_day_of_week === 1) {
                array_push($days_of_week, array_shift($days_of_week)); // Make Monday first
            }

            ob_start();
            ?>
            <div class="calendar-container">
                <?php if ($options['header'] || $options['navigation']): ?>
                <div class="calendar-header">
                    <?php if ($options['navigation']): ?>
                    <button type="button" class="calendar-nav calendar-prev" data-year="<?= $prev_month->format('Y') ?>" data-month="<?= $prev_month->format('m') ?>">
                        &lt;
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($options['header']): ?>
                    <div class="calendar-month-year"><?= $month_name ?></div>
                    <?php endif; ?>
                    
                    <?php if ($options['navigation']): ?>
                    <button type="button" class="calendar-nav calendar-next" data-year="<?= $next_month->format('Y') ?>" data-month="<?= $next_month->format('m') ?>">
                        &gt;
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="calendar-grid">
                    <?php foreach ($days_of_week as $day): ?>
                        <div class="calendar-day-header"><?= $day ?></div>
                    <?php endforeach; ?>
                    
                    <?php // Previous month's days
                    if ($this->show_other_months) {
                        for ($i = $first_day_of_week; $i > 0; $i--) {
                            $day_num = $num_days_last_month - $i + 1;
                            $date_str = $prev_month->format('Y-m') . '-' . str_pad($day_num, 2, '0', STR_PAD_LEFT);
                            ?>
                            <div class="calendar-day other-month" data-date="<?= $date_str ?>">
                                <?= $day_num ?>
                            </div>
                            <?php
                        }
                    } else {
                        for ($i = $first_day_of_week; $i > 0; $i--) {
                            ?><div class="calendar-day empty"></div><?php
                        }
                    }
                    
                    // Current month's days
                    for ($i = 1; $i <= $num_days; $i++) {
                        $date_str = "{$this->active_year}-{$this->active_month}-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                        $date_obj = new DateTime($date_str, $this->timezone);
                        $day_classes = ['calendar-day'];
                        
                        // Check if today
                        $today = new DateTime('now', $this->timezone);
                        if ($this->highlight_today && $date_str === $today->format('Y-m-d')) {
                            $day_classes[] = 'today';
                        }
                        
                        // Check if selected day
                        if ($i == $this->active_day) {
                            $day_classes[] = 'selected';
                        }
                        
                        // Check for events
                        $has_events = false;
                        foreach ($this->events as $event) {
                            $event_date = new DateTime($event['date'], $this->timezone);
                            $end_date = (clone $event_date)->modify('+' . ($event['days'] - 1) . ' days');
                            
                            if ($date_obj >= $event_date && $date_obj <= $end_date) {
                                $has_events = true;
                                $day_classes[] = 'has-event' . $event['color'];
                                break;
                            }
                        }
                        ?>
                        <div class="<?= implode(' ', $day_classes) ?>" data-date="<?= $date_str ?>">
                            <span class="day-number"><?= $i ?></span>
                            <?php if ($has_events): ?>
                            <div class="event-indicator"></div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    
                    // Next month's days
                    $days_shown = $first_day_of_week + $num_days;
                    $days_needed = ceil($days_shown / 7) * 7;
                    
                    if ($this->show_other_months) {
                        for ($i = 1; $i <= ($days_needed - $days_shown); $i++) {
                            $date_str = $next_month->format('Y-m') . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                            ?>
                            <div class="calendar-day other-month" data-date="<?= $date_str ?>">
                                <?= $i ?>
                            </div>
                            <?php
                        }
                    } else {
                        for ($i = 1; $i <= ($days_needed - $days_shown); $i++) {
                            ?><div class="calendar-day empty"></div><?php
                        }
                    }
                    ?>
                </div>
                
                <?php if ($options['footer']): ?>
                <div class="calendar-footer">
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <span class="legend-color available"></span>
                            <span class="legend-text">Available</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php
            return ob_get_clean();
        } catch (Exception $e) {
            return '<div class="calendar-error">Error rendering calendar</div>';
        }
    }

    public function __toString() {
        return $this->render();
    }
}
?>