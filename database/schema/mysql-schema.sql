/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignments`
(
    `id`       bigint unsigned NOT NULL,
    `type`     varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `mimes`    json                                    NOT NULL,
    `deadline` timestamp                               NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `assignments_id_foreign` FOREIGN KEY (`id`) REFERENCES `posts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments`
(
    `id`                  bigint unsigned NOT NULL AUTO_INCREMENT,
    `attachmentable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `attachmentable_id`   bigint unsigned NOT NULL,
    `submission_id`       bigint unsigned NOT NULL,
    `owner_id`            bigint unsigned NOT NULL,
    `name`                varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `path`                varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY                   `attachments_attachmentable_type_attachmentable_id_index` (`attachmentable_type`,`attachmentable_id`),
    KEY                   `attachments_submission_id_foreign` (`submission_id`),
    KEY                   `attachments_owner_id_foreign` (`owner_id`),
    CONSTRAINT `attachments_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
    CONSTRAINT `attachments_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings`
(
    `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `faculty_id` bigint unsigned DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `buildings_name_unique` (`name`),
    KEY          `buildings_faculty_id_foreign` (`faculty_id`),
    CONSTRAINT `buildings_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache`
(
    `key`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `value`      mediumtext COLLATE utf8mb4_unicode_ci   NOT NULL,
    `expiration` int                                     NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks`
(
    `key`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `owner`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `expiration` int                                     NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses`
(
    `id`                bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`              varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `study_program_id`  bigint unsigned DEFAULT NULL,
    `semester_required` tinyint unsigned NOT NULL,
    `semester_parity`   varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `is_elective`       bool                                    NOT NULL,
    `credits`           tinyint unsigned NOT NULL,
    `created_at`        timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `courses_name_study_program_id_unique` (`name`,`study_program_id`),
    KEY                 `courses_study_program_id_foreign` (`study_program_id`),
    CONSTRAINT `courses_study_program_id_foreign` FOREIGN KEY (`study_program_id`) REFERENCES `study_programs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `faculties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculties`
(
    `id`            bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`          varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `accreditation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at`    timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `faculties_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs`
(
    `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
    `uuid`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `connection` text COLLATE utf8mb4_unicode_ci         NOT NULL,
    `queue`      text COLLATE utf8mb4_unicode_ci         NOT NULL,
    `payload`    longtext COLLATE utf8mb4_unicode_ci     NOT NULL,
    `exception`  longtext COLLATE utf8mb4_unicode_ci     NOT NULL,
    `failed_at`  timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches`
(
    `id`             varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name`           varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `total_jobs`     int                                     NOT NULL,
    `pending_jobs`   int                                     NOT NULL,
    `failed_jobs`    int                                     NOT NULL,
    `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci     NOT NULL,
    `options`        mediumtext COLLATE utf8mb4_unicode_ci,
    `cancelled_at`   int DEFAULT NULL,
    `created_at`     int                                     NOT NULL,
    `finished_at`    int DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs`
(
    `id`           bigint unsigned NOT NULL AUTO_INCREMENT,
    `queue`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `payload`      longtext COLLATE utf8mb4_unicode_ci     NOT NULL,
    `attempts`     tinyint unsigned NOT NULL,
    `reserved_at`  int unsigned DEFAULT NULL,
    `available_at` int unsigned NOT NULL,
    `created_at`   int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY            `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations`
(
    `id`        int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `batch`     int                                     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens`
(
    `email`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `token`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`email`),
    CONSTRAINT `password_reset_tokens_email_foreign` FOREIGN KEY (`email`) REFERENCES `users` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts`
(
    `id`           bigint unsigned NOT NULL AUTO_INCREMENT,
    `subject_id`   bigint unsigned NOT NULL,
    `title`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `content`      text COLLATE utf8mb4_unicode_ci,
    `type`         varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `published_at` timestamp NULL DEFAULT NULL,
    `created_at`   timestamp NULL DEFAULT NULL,
    `updated_at`   timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY            `posts_subject_id_foreign` (`subject_id`),
    CONSTRAINT `posts_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `professors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `professors`
(
    `id`         bigint unsigned NOT NULL,
    `user_id`    bigint unsigned NOT NULL,
    `faculty_id` bigint unsigned DEFAULT NULL,
    `status`     varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY          `professors_user_id_foreign` (`user_id`),
    KEY          `professors_faculty_id_foreign` (`faculty_id`),
    CONSTRAINT `professors_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`),
    CONSTRAINT `professors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms`
(
    `id`          bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `capacity`    tinyint unsigned NOT NULL,
    `building_id` bigint unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `rooms_name_building_id_unique` (`name`,`building_id`),
    KEY           `rooms_building_id_foreign` (`building_id`),
    CONSTRAINT `rooms_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions`
(
    `id`            varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_id`       bigint unsigned DEFAULT NULL,
    `ip_address`    varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent`    text COLLATE utf8mb4_unicode_ci,
    `payload`       longtext COLLATE utf8mb4_unicode_ci     NOT NULL,
    `last_activity` int                                     NOT NULL,
    PRIMARY KEY (`id`),
    KEY             `sessions_user_id_index` (`user_id`),
    KEY             `sessions_last_activity_index` (`last_activity`),
    CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff`
(
    `id`         bigint unsigned NOT NULL,
    `user_id`    bigint unsigned NOT NULL,
    `faculty_id` bigint unsigned DEFAULT NULL,
    `status`     varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY          `staff_user_id_foreign` (`user_id`),
    KEY          `staff_faculty_id_foreign` (`faculty_id`),
    CONSTRAINT `staff_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`),
    CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students`
(
    `id`               bigint unsigned NOT NULL,
    `user_id`          bigint unsigned NOT NULL,
    `study_program_id` bigint unsigned NOT NULL,
    `hometown`         varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `enrollment_type`  varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `parent_name`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `parent_phone`     varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `parent_address`   varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `parent_job`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `status`           varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY                `students_user_id_foreign` (`user_id`),
    KEY                `students_study_program_id_foreign` (`study_program_id`),
    CONSTRAINT `students_study_program_id_foreign` FOREIGN KEY (`study_program_id`) REFERENCES `study_programs` (`id`),
    CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `study_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `study_programs`
(
    `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `faculty_id` bigint unsigned NOT NULL,
    `level`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `study_programs_name_unique` (`name`),
    KEY          `study_programs_faculty_id_foreign` (`faculty_id`),
    CONSTRAINT `study_programs_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subject_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subject_group_members`
(
    `subject_group_id` bigint unsigned NOT NULL,
    `student_id`       bigint unsigned NOT NULL,
    UNIQUE KEY `subject_group_members_subject_group_id_student_id_unique` (`subject_group_id`,`student_id`),
    KEY                `subject_group_members_student_id_foreign` (`student_id`),
    CONSTRAINT `subject_group_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
    CONSTRAINT `subject_group_members_subject_group_id_foreign` FOREIGN KEY (`subject_group_id`) REFERENCES `subject_groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subject_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subject_groups`
(
    `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `subject_id` bigint unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY          `subject_groups_subject_id_foreign` (`subject_id`),
    CONSTRAINT `subject_groups_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects`
(
    `id`           bigint unsigned NOT NULL AUTO_INCREMENT,
    `course_id`    bigint unsigned NOT NULL,
    `professor_id` bigint unsigned NOT NULL,
    `room_id`      bigint unsigned DEFAULT NULL,
    `capacity`     tinyint unsigned NOT NULL,
    `parallel`     char(255) COLLATE utf8mb4_unicode_ci    NOT NULL,
    `code`         varchar(3) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `note`         text COLLATE utf8mb4_unicode_ci,
    `day`          varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `start_time`   time                                    NOT NULL,
    `year` year NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subjects_course_id_parallel_year_unique` (`course_id`,`parallel`,`year`),
    KEY            `subjects_professor_id_foreign` (`professor_id`),
    KEY            `subjects_room_id_foreign` (`room_id`),
    CONSTRAINT `subjects_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
    CONSTRAINT `subjects_professor_id_foreign` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`),
    CONSTRAINT `subjects_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions`
(
    `id`                  bigint unsigned NOT NULL AUTO_INCREMENT,
    `assignment_id`       bigint unsigned NOT NULL,
    `submissionable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `submissionable_id`   bigint unsigned NOT NULL,
    `note`                varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `score`               tinyint unsigned NOT NULL,
    `submitted_at`        timestamp NULL DEFAULT NULL,
    `scored_at`           timestamp NULL DEFAULT NULL,
    `updated_at`          timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY                   `submissions_assignment_id_foreign` (`assignment_id`),
    KEY                   `submissions_submissionable_type_submissionable_id_index` (`submissionable_type`,`submissionable_id`),
    CONSTRAINT `submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users`
(
    `id`                bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`              varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email`             varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `phone_number`      varchar(16) COLLATE utf8mb4_unicode_ci  NOT NULL,
    `gender`            varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password`          varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `role`              varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `remember_token`    varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at`        timestamp NULL DEFAULT NULL,
    `updated_at`        timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    UNIQUE KEY `users_phone_number_unique` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (4, '2024_03_14_201243_create_faculties_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (5, '2024_03_14_201602_create_study_programs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (6, '2024_03_14_202108_create_staff_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (7, '2024_03_14_202438_create_professors_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (8, '2024_03_14_203048_create_students_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (9, '2024_03_25_171502_create_buildings_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (10, '2024_03_25_171521_create_rooms_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (11, '2024_03_25_171542_create_courses_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (12, '2024_03_25_171559_create_subjects_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (13, '2024_03_25_171602_create_posts_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (14, '2024_03_25_171608_create_assignments_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (15, '2024_03_25_171619_create_subject_groups_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (16, '2024_03_25_171627_create_subject_group_members_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (17, '2024_03_25_171652_create_submissions_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (18, '2024_03_25_225228_create_attachments_table', 1);
