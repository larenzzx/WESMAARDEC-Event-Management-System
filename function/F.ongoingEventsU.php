<?php
// Include your database connection code here
require_once('../db.connection/connection.php');

// Check if UserID is set in the session
if (isset($_SESSION['UserID'])) {
    $UserID = $_SESSION['UserID'];

    // Fetch events that the user has joined from the database, excluding canceled events
    $sql = "SELECT Events.*, EventParticipants.UserID
            FROM Events
            INNER JOIN EventParticipants ON Events.event_id = EventParticipants.event_id
            WHERE EventParticipants.UserID = ? AND (Events.event_cancel IS NULL OR Events.event_cancel = '')
            ORDER BY Events.date_created DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get current date and time
    $currentDateTime = new DateTime('now', new DateTimeZone('Asia/Manila'));

    // Variable to track if there are ongoing events
    $hasOngoingEvents = false;

    // Loop through each event and generate a table row for ongoing events
    while ($row = mysqli_fetch_assoc($result)) {
        $eventTitle = $row['event_title'];
        $eventLocation = $row['location'];
        $eventDateStart = date('F j, Y', strtotime($row['date_start'])); // Format date as Month day, Year
        $eventDateEnd = date('F j, Y', strtotime($row['date_end'])); // Format date as Month day, Year
        $eventTimeStart = date('h:ia', strtotime($row['time_start'])); // Format time as Hour:Minute AM/PM
        $eventTimeEnd = date('h:ia', strtotime($row['time_end'])); // Format time as Hour:Minute AM/PM
        $eventMode = $row['event_mode'];
        $eventType = $row['event_type'];
        $eventId = $row['event_id'];

        // Get start and end datetime of the event
        $eventStartDateTime = new DateTime($row['date_start'] . ' ' . $row['time_start'], new DateTimeZone('Asia/Manila'));
        $eventEndDateTime = new DateTime($row['date_end'] . ' ' . $row['time_end'], new DateTimeZone('Asia/Manila'));

        // Check if the current date and time are between start and end time of the event
        if ($currentDateTime >= $eventStartDateTime && $currentDateTime <= $eventEndDateTime) {
            // Display ongoing events
            $eventStatus = 'ongoing';
            $hasOngoingEvents = true;

            echo '<tr data-start-date="' . $row['date_start'] . '" data-end-date="' . $row['date_end'] . '">';
            ?>
            <td data-label="Event Title"><?php echo $eventTitle; ?></td>
            <td data-label="Event Type"><?php echo $eventType; ?></td>
            <td data-label="Event Mode"><?php echo $eventMode; ?></td>
            <td data-label="Event Location"><?php echo $eventLocation; ?></td>
            <td data-label="Event Date"><?php echo "$eventDateStart - $eventDateEnd"; ?></td>
            <td data-label="Event Time"><?php echo "$eventTimeStart - $eventTimeEnd"; ?></td>
            <td data-label="Status"><?php echo $eventStatus; ?></td>
            <td data-label="Attendance" class="pad">
                <a href="myEvent.php?event_id=<?php echo $eventId; ?>"><button class="btn_edit"><i
                            class="fa-solid fa-eye"></i></button></a>
            </td>
            <?php
            echo '</tr>';
        }
    }

    // Check if there are no ongoing events
    if (!$hasOngoingEvents) {
        echo "<tr><td colspan='8' style='text-align: center;'>No Ongoing Events!</td></tr>";
    }

    // Close the result set
    mysqli_free_result($result);

    // Close database connection
    mysqli_close($conn);
} else {
    // Redirect to login page or handle the case where UserID is not set in the session
    header("Location: login.php");
    exit();
}
?>