<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141001165224 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Repair passwords from backup
        $this->addSql('UPDATE users set password = "$2y$10$Pdu.e2etxAfReIc2qzAKyu7VHZw7H4sFWZoKTtfbeibnslsxdHVZ2" where email = "prossol@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$MEGCP.4Jkh86RqDugc4IN.ADUDsmBMzun.Ah3G8yb/c9SXKI2BPVW" where email = "vdouglas@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$uFGts.IWk1b.SDNW/HsJGupqgV9ls9lu46H0.2dwHRrWUinbaWeCO" where email = "mtaylo24@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$DSxCivsRnd7sRD676nT9CewCck3h.jFsAiAdk7IfYycxtgenRV3y." where email = "jhoyer@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$sOJQ3PT.kAy2cWLZ6RlQhOwbJBtbsU1h.FhBFv.ysLgQcGbI4nzVa" where email = "jseybert@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$05gX8sopWQ6S8oQvvdkg4.lhLfSnKbbpSI4s81AshVRMlE/sbg5wC" where email = "Rkirshstein@air.org" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$v7QO00OXBA8P6asVXIoTdeo/Rmwy7ygo7JnVpmXFNSR5UxaK7iumq" where email = "srider@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$IDAsyUNPy9Ed0WY9pfiE3eFE9Bnas0ut/IselqnwRhSC3SX/xQMAe" where email = "mou@wccnet.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$dx0xS1IXvgk5mz06DOxWd./V5ucwbZIR5od9g18l1qBXtl/loJT8y" where email = "weverin@wccnet.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$HVxyRALIev934hNpLiIQYOwwsDs8TzOA0kSfLplx0GdIMh9hDdcLS" where email = "btelford@edisonohio.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$P85mgNKx8FjB472lEjWKPO2yHXKYpMrQUe83UTisyAy1l.nAgE372" where email = "shollima@indianhills.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$3YwmrL6t7XXfDy4Agtp/J.SSzRCjhYXHBXs0K/EqfhAQegqsKOq3O" where email = "slevitzke@lbwcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$hvhimuEgVThhXCWcaBt8tuz6Tv0RbKhTJB637s0R2SyXjDNtTrSuC" where email = "jcdehart@dmacc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "jacalyn.askin@cgc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$7ZIl/8o5ecpccc5ClhsZx.cZEaU4o2tJCAFLeFzO.8dB5Jh/nGJ3." where email = "theresa.wong@cgc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$dXZKY2ry.nwu1Rz2Y9w2S.CXtzwb8kzMOQPqSHkDAU3bPZUBxGgeO" where email = "ssolander@neosho.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$5Cs9SJZg/QZV3zVLjszE3uwN0H3GjiGFfvp5Ax6vxslmaZsmK5Oai" where email = "lhauser@neosho.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$KW3IkJdweNelcs8CjcR5cOg3N0Ne5H/TAAoMFIN04KrmPD6nFMK6O" where email = "landerson7@nwacc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$SfjHXdcz.VoMXYhX4Pqa1eaEeAccDPt6Hgu3pXyvPTBC8rpL1ScuK" where email = "kpurdy@nwacc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$.NRhaidVpeS/nbZPZYpaeeAz0bCYWNB3aFpbLZ1snplPzebbiXODW" where email = "bourgeoisj@sccsc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Ow0fIGUMHcATVPV7oQcAHuJnmNv1SuW4NFfXgN69NccXDjbqJllOG" where email = "michael.berndt@normandale.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "John.Madden@roswell.enmu.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$UsHmgppBJmAK5ageQ90xyu88KZFzYCK9TyhgiAzYTlzn7rAmpY0Xy" where email = "rhonda.crocker@roswell.enmu.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$KW4UuL8ABI9JPiEHpONkIe3PkKctQslEXhJTIK4RZpUt.3KfAcxy6" where email = "brittany.inge@kctcs.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$vFZwKuboWyeLTlqxNN9oVePofIYqqMi9PfX6obPEt077zM2H3yjFS" where email = "pusinger@polk.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$dff2vhWScA4S8DEU0Udtb.d1z/Ryi86.2hFHjy.gd9APPVWeJBy2u" where email = "gsorrell@sfccmo.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$NdJbGiW7MwtjbAeaVEaJ.eYT4HuORLVOXlKU9BV7XcgvxBw96LJIO" where email = "bappleton@sfccmo.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$9H.4JpUUrPvuvKt1.4aOne7ytQwzhZf0Og01ojO6lFN5DisoCLfWq" where email = "wmarson@inverhills.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$FiyfVcUgQMGIiZSX7Euht.bo2tRFbIw0VLnWFZ1XiLZ1.1Ek7KKI6" where email = "kris.binard@frontrange.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$3SbP6te3j0GUqlfz6Y7.becHR7ZeXkT1urf2NyFL.6diQw4MpkpMW" where email = "khjordan@waketech.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$32whu0cE.Yw5tYaepFSQve8R5i9kB2X8AHMWdbPT5lbAUHSlOoCpC" where email = "jeremy.berberich@kctcs.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$CeyimXcSDBycvUVM7vNcdOM1QVyBhBROmSSgkDujqspiDGzjKym2y" where email = "steve.popple@kctcs.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$mXR9e2CWrwPswriIGIWOMOO42IIrdv3t7fnfLt.CRHZsmA.W7Cu8S" where email = "ceubank@trcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Z3LbdeMV9Sqrk02VxeMCl.QXnHTcDC7IUxKPoXM2o46kFyMP10SJ6" where email = "Edward.Hummingbird@bie.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$sPG4M/Kr6zejdZG9eaZA..khrPNg8pG8nt7LPc6kPBkokArDU0y8q" where email = "christopher.t.tkach@lonestar.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "deseree.m.probasco@lonestar.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$cSwY6FQmULqCBSto1BIyKelYf0d9vq0mKn0g20LF7kZJxLJfAJlXa" where email = "kristy.bishop@mcckc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$q7KUF5kOfo6uFdeWCF9Q4uXBk1UnS6Ry102cWBeBrhMarCv2K6nwy" where email = "lfreiburger@grcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$b85USfYrugNnBYvRdBazLO6UBrqGApMttvSqjwsvzsbBbM6Ouge7i" where email = "lance@hawaii.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "heilmanc@bartonccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$MrrnWjneCWHvt/VtVZ7rWuJTEvIDVRjWpEl5HQ0dB9sRGwFPRLCSa" where email = "crutcherc@bartonccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$VLf..WjN46hhcVfrMJKasedtGQm5wI0oN5nVnxDehhhJE2tGRw7sO" where email = "tmartin@collin.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$T19eg6ELGQV7HFnwEoxJRe4dgdA4QYaRtkMmzw9PFMQ/uvuXs8JTi" where email = "nahmad@collin.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$OexLwinYhx1Xwvv1RmfeguVqA6aTT8mQV4743.nABWEzd5U3SlqF2" where email = "larry.obermeyer@witcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "erin.volk@witcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "maryh@northeast.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$.zGmKSzFGwnZKXMFlGo7nuBa8AtrzHjQaQ7IzS2yLkBczoDFwIVlq" where email = "juliem@northeast.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$4k2RXyrI7xKRWy99RhA4A.Y7MFernpn2x8xfensm0lqjZAjbMy2km" where email = "cathy.almquist@tridenttech.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "kleach@wwcc.wy.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "jfreeze@wwcc.wy.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$D4SWMF.HAARLA4o3BIjZEeynOf39JmlJBUWlEBqGg8usWVpQTFCBa" where email = "louguthrie@jccc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$DNP0pIoSNMw/V9uIvPis0engaFHXYFCMenvX8n39fEZ6AeWLLWrI2" where email = "malcolmk@bhc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Rvfa3jse4SJFePV4KCJ/pugZMVev2V6qbYHCmzwIgtwwsuJEEF0zG" where email = "huntleyd@bhc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$GIGcfwKKfjLCDdBfAPQru.By9i8HUgF0ot1F7Zg9rYUXNqJicHKRa" where email = "kddavis@actx.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "melissa.giese@mcckc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$kjsvpI8pHclURXYY/Gf1r.DqLeliqCxOyx3n4VLMmqMLxcUJ27ZDi" where email = "jennifer.arth@mcckc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "deanna.burgard@bismarckstate.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$eigi4CgIi1RB0N4E9QjPXu2lqQvJa984sp1pn8bPPf2m18LJCnNna" where email = "carla.hixson@bismarckstate.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$ti/LoGgjbRMofLKXAE01e.tFNwkNdz39WJ/3nFZCKWwTxtLGRz.4a" where email = "mona.rabon@cpcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$srGlkeitNryi0J85Gm8BWutWDKX5UF.8wJBY7hXmoU.b1AB4tFMQy" where email = "matias.garza@hccs.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$TyDZTk5SC1bLkVNJAxuKeeatP/XF2yXSig1rAwV9l6LuSlrcgLBlq" where email = "michael.bankey@tri-c.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Qc.mcpsdgzztKdKcVH2dNO0fhgx90e283kh.nxbc.bIngLtUKODOS" where email = "clarkm@cravencc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Ta0cR6HB.TQird97GSYJzO0x.NPKgU4O7Hld4LGBh.8.J2iUvvPce" where email = "mitchelle@cravencc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$uMM0R5S.n72lUK9YBv.J1OiXAksjpeGc7s4lpOeYqZrgzMMI567O2" where email = "olga.fedotova@pcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$3fT4LifM6I/h6GkXFUC.muH/7zPrZgshNq2.JQO8tpyZInDhkMnLm" where email = "pmurray@pcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$DFzS8il6aC26mpKjJgcI4uvBPHFXEDufcQxiS4yLwpkLQ63uWeu0m" where email = "mokeefe@germanna.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$CqzYbeohlNrtsZ9P5ZI5U.z17xNSmdMOqShvG0zyBQ56LxtYBe84K" where email = "jrogers@iowalakes.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "rbeernink@iowalakes.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$e9IP9Uo.YTBh4wRj4U6HDuvXbGz7tlTRTRWj0N5uFr2szzDN/SEja" where email = "dburke4@cnm.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$aTnP0Si7qxX3Eoz28fhCReeGgUs7f2HRTkhuMhdhHrEVl5XJED7K2" where email = "evdow@cnm.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$qVp497HtYON7tbfIykwWGeMBR.FGXouTPB5ZZbqkDvrdP28WD1oqu" where email = "edith.armey@lrsc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "scott.williams@tulsatech.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$U5Ne9GdHpLLMwAKtQTlqDOMDOHEzShc7HelTD5qLT75WeZriqBqQW" where email = "melissa.oates@tulsatech.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$dZbBc6yeZFB7byCX3dwc1uFn6VhJQYfyKStmIbRxvptX3z6KKB2IG" where email = "kirkb@midlandstech.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Q4V6BIb.OabaPuh1Q8P1PeLDeOOQfHFtZXpj82cBqV304uk8cTwrW" where email = "kim.becicka@kirkwood.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "brenda.ireland@kirkwood.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$NxiE9wEzvRtfM2DvjGzUS.TrPS7CzjDBe3coJqsSjt0utRUJEfHGS" where email = "gpluczak@delta.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$ZJl2SAH9f9AdjTZ/tfDw5O3yUebenPtQsZfKYU3o8MvAt5mY/5lsK" where email = "lindseybourassa@delta.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "deanette.piesik@willistonstate.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$b7ScuBF7lTOoGWtJVPFrnOJ/RpMZOeeL9.X3MN2YeBQqxLZBtTjSu" where email = "kasey.anderson@willistonstate.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$bfYH3NjbWVvFJXbVqruKXu1c9Nkdhp/14p8IDghHI90TFccY8Fbo6" where email = "drenz@wwcc.wy.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$cNan98CyTyo.FsHck9FufeXvoTNLWZoAteF.1varKyNJXPMnvm53u" where email = "LeAnn.Blankenburg@witcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$EacAHYrrppn/i/Jd54jezuiMDIsonZHFWlt1iAGhDCa3xPk/7/l5." where email = "runge@cayuga-cc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$oYDVa6WEH7v6tNbnLVx.suqzLJlXj47tV.0M6Kgzxr/YOfi6rqRo." where email = "cng@miracosta.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "garciab@lcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$tHIR0L65xUnDUwlIyVuGseq/zVETDqlIxmrycFbfBXSj.4LENakUK" where email = "theresa.monson@willistonstate.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$dvjp3LJmtIG3cKus5jrcbOEgafiLVvwjSlkzn/zU0mb7srHknUfz." where email = "smithte@tcc.fl.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$dj95sievdy92ezL1Efr6lO44jIOrwvsydmK0TsqMsXJqfy8Te7JNS" where email = "linda.macminn@fkcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$aiJCn.c.ZHJ6u5ws0i6P..ZMdYyuB/eTw6sqb9XlKxSsZm9fp/H4m" where email = "jonathan.gueverra@fkcc.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "m0163300@actx.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$Eo3X/aiqZDTcFCG5K5AECe4fUNoBmydE5l6hlgVt7tlRX9dDSpda6" where email = "dlmcanally@actx.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$10$2dFdg2STNzXiJtPnJhUX6eXsFEiv/ji3oVpaY1i4jDg7n0kXYemra" where email = "mattie.hudson@wallacestate.edu" AND length(password) < 60;');
        $this->addSql('UPDATE users set password = "$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC" where email = "vicki.hawsey@wallacestate.edu" AND length(password) < 60;');


    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
