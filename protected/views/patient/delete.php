<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

$clinical = $clinical = $this->checkAccess('OprnViewClinical');
$warnings = $this->patient->getWarnings($clinical);
?>

<div class="container content">
	<h1 class="badge">Delete patient</h1>
	<div class="messages patient">
		<?php $this->renderPartial('//base/_messages'); ?>

		<?php if ($this->patient->contact->address && !$this->patient->contact->address->isCurrent()) {?>
			<div class="row">
				<div class="large-12 column">
					<div id="no-current-address-error" class="alert-box alert with-icon">
						Warning: The patient has no current address. The address shown is their last known address.
					</div>
				</div>
			</div>
		<?php }?>

		<?php if ($this->patient->isDeceased()) {?>
			<div clas="row">
				<div class="large-12 column">
					<div id="deceased-notice" class="alert-box alert with-icon">
						This patient is deceased (<?php echo $this->patient->NHSDate('date_of_death'); ?>)
					</div>
				</div>
			</div>
		<?php }?>

		<?php if (!$this->patient->practice || !$this->patient->practice->contact->address) {?>
			<div class="row">
				<div class="large-12 column">
					<div id="no-practice-address" class="alert-box alert with-icon">
						Patient has no GP practice address, please correct in PAS before printing GP letter.
					</div>
				</div>
			</div>
		<?php }?>

		<?php if ($warnings) { ?>
			<div class="row">
				<div class="large-12 column">
					<div class="alert-box patient with-icon">
						<?php foreach ($warnings as $warn) {?>
							<strong><?php echo $warn['long_msg']; ?></strong>
							- <?php echo $warn['details'];
						}?>
					</div>
				</div>
			</div>
		<?php }?>
	</div>

	<div class="row">
		<div class="large-6 column">
			<?php $this->renderPartial('_patient_details',array('patient' => $this->patient, 'no_edit' => true))?>
			<?php $this->renderPartial('_patient_contact_details',array('patient' => $this->patient, 'no_edit' => true))?>
			<?php $this->renderPartial('_patient_gp',array('no_edit' => true))?>
			<?php $this->renderPartial('_patient_commissioningbodies')?>
		</div>
		<div class="large-6 column">
			<?php $this->renderPartial('_delete_patient')?>
		</div>
	</div>
</div>
